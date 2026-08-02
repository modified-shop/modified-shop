/* --------------------------------------------------------------
 $Id$

 modified eCommerce Shopsoftware
 http://www.modified-shop.org

 Copyright (c) 2009 - 2019 [www.modified-shop.org]
 --------------------------------------------------------------
 Released under the GNU General Public License
 --------------------------------------------------------------*/

const baseRequest = {
  apiVersion: 2,
  apiVersionMinor: 0,
};
let paymentsClient = null,
  allowedPaymentMethods = null,
  merchantInfo = null;

// reports otherwise-invisible client-side Google Pay failures (SDK
function reportGooglePayError(step, err) {
  try {
    var payload = new URLSearchParams({
      token: window.paypalClientErrorToken || "",
      step: step,
      name: err && err.name ? err.name : "",
      message: err && err.message ? err.message : String(err),
    });
    if (navigator.sendBeacon) {
      navigator.sendBeacon(DIR_WS_BASE + "ajax.php?ext=log_paypal_client_error&payment_method=paypalgooglepay", payload);
    } else {
      $.post(DIR_WS_BASE + "ajax.php?ext=log_paypal_client_error&payment_method=paypalgooglepay", {
        token: window.paypalClientErrorToken || "",
        step: step,
        name: err && err.name ? err.name : "",
        message: err && err.message ? err.message : String(err),
      });
    }
  } catch (e) {
    // reporting must never itself break the payment flow
  }
}

function getGoogleIsReadyToPayRequest(allowedPaymentMethods) {
  return Object.assign({}, baseRequest, {
    allowedPaymentMethods: allowedPaymentMethods,
  });
}

async function getGooglePayConfig() {
  if (allowedPaymentMethods == null || merchantInfo == null) {
    const googlePayConfig = await paypal.Googlepay().config();
    allowedPaymentMethods = googlePayConfig.allowedPaymentMethods;
    merchantInfo = googlePayConfig.merchantInfo;
  }
  return {
    allowedPaymentMethods,
    merchantInfo,
  };
}

async function getGooglePaymentDataRequest() {
  const paymentDataRequest = Object.assign({}, baseRequest);
  const { allowedPaymentMethods, merchantInfo } = await getGooglePayConfig();
  paymentDataRequest.allowedPaymentMethods = allowedPaymentMethods;
  paymentDataRequest.transactionInfo = getGoogleTransactionInfo();
  paymentDataRequest.merchantInfo = merchantInfo;
  // PayPal's own Googlepay().config() returns allowedPaymentMethods with
  // billingAddressRequired/assuranceDetailsRequired set for this merchant,
  // which Google requires a PAYMENT_METHOD callback intent (and therefore
  // onPaymentDataChanged) for - otherwise loadPaymentData() rejects with
  // DEVELOPER_ERROR. googlePaymentDataChangedHandler defaults to null here
  // (only the cart flow sets it), so the wrapper in getGooglePaymentsClient()
  // just resolves with no update, which is all that's needed for checkout.
  paymentDataRequest.callbackIntents = ["PAYMENT_AUTHORIZATION", "PAYMENT_METHOD"];
  return paymentDataRequest;
}

function onPaymentAuthorized(paymentData) {
  return new Promise(function (resolve, reject) {
    processPayment(paymentData)
      .then(function (data) {
        resolve({ transactionState: "SUCCESS" });
      })
      .catch(function (errDetails) {
        reportGooglePayError("onPaymentAuthorized", errDetails);
        resolve({ transactionState: "ERROR" });
      });
  });
}

// the checkout flow (process_button() in paypalgooglepay.php) and the cart
// flow (setupGooglepayCart() below) each need their own onPaymentAuthorized/
// onPaymentDataChanged behavior, but share a single PaymentsClient instance -
// these are reassigned by whichever flow actually runs on the current page
let googlePaymentAuthorizedHandler = onPaymentAuthorized;
let googlePaymentDataChangedHandler = null;

function getGooglePaymentsClient() {
  if (paymentsClient === null) {
    paymentsClient = new google.payments.api.PaymentsClient({
      environment: getGoogleEnviroment(),
      paymentDataCallbacks: {
        onPaymentAuthorized: function (paymentData) {
          return googlePaymentAuthorizedHandler(paymentData);
        },
        onPaymentDataChanged: function (intermediatePaymentData) {
          return googlePaymentDataChangedHandler
            ? googlePaymentDataChangedHandler(intermediatePaymentData)
            : Promise.resolve({});
        },
      },
    });
  }
  return paymentsClient;
}

async function onGooglePayLoaded() {
  const paymentsClient = getGooglePaymentsClient();
  const { allowedPaymentMethods } = await getGooglePayConfig();
  paymentsClient
    .isReadyToPay(getGoogleIsReadyToPayRequest(allowedPaymentMethods))
    .then(function (response) {
      if (response.result) {
        addGooglePayButton();
      }
    })
    .catch(function (err) {
      reportGooglePayError("isReadyToPay", err);
      console.error(err);
    });
}

async function onGooglePaymentButtonClicked() {
  const paymentDataRequest = await getGooglePaymentDataRequest();
  paymentDataRequest.transactionInfo = getGoogleTransactionInfo();

  const paymentsClient = getGooglePaymentsClient();
  paymentsClient.loadPaymentData(paymentDataRequest);
}

async function processPayment(paymentData) {
  try {
    const { currencyCode, totalPrice } = getGoogleTransactionInfo();
    const order = {
      intent: "CAPTURE",
      purchase_units: [
        {
          amount: {
            currency_code: currencyCode,
            value: totalPrice,
          },
        },
      ],
    };

    /* Create Order */
    const id = getGoogleOrderID();

    const { status } = await paypal.Googlepay().confirmOrder({
      orderId: id,
      paymentMethodData: paymentData.paymentMethodData,
    });

    if (status === "PAYER_ACTION_REQUIRED") {
      await paypal.Googlepay().initiatePayerAction({ orderId: id });

      const orderResponse = await fetch(DIR_WS_BASE+'ajax.php?ext=check_paypal_order&payment_method=paypalgooglepay');
      if (!orderResponse.ok || (await orderResponse.json()) !== true) {
        throw new Error(orderResponse.ok ? "liability shift check failed" : "HTTP " + orderResponse.status);
      }

      redirectGoogleSuccess();
    } else if (status === "APPROVED") {
      redirectGoogleSuccess();
    } else {
      throw new Error("Google Pay order not approved: " + status);
    }
  } catch (err) {
    reportGooglePayError("processPayment", err);
    redirectGoogleError();
    throw err;
  }
}


async function setupGooglepayCart() {
  const { allowedPaymentMethods, merchantInfo } = await getGooglePayConfig();
  const paymentsClient = getGooglePaymentsClient();

  const readiness = await paymentsClient.isReadyToPay(getGoogleIsReadyToPayRequest(allowedPaymentMethods));
  if (!readiness.result) {
    return;
  }

  // request a full billing address for the cart flow only - the checkout
  // flow already knows the customer's billing address, so it doesn't ask
  // for it - clone before mutating, allowedPaymentMethods is cached/shared
  // with the checkout flow
  const cartPaymentMethods = JSON.parse(JSON.stringify(allowedPaymentMethods));
  const cardMethod = cartPaymentMethods.find((method) => method.type === "CARD") || cartPaymentMethods[0];
  if (cardMethod) {
    cardMethod.parameters = Object.assign({}, cardMethod.parameters, {
      billingAddressRequired: true,
      billingAddressParameters: { format: "FULL", phoneNumberRequired: true },
    });
  }

  // Created for each payment attempt in the click handler below and shared
  // with the shipping/payment callbacks fired by that Google Pay sheet.
  let orderIdPromise = null;

  googlePaymentDataChangedHandler = async function (intermediatePaymentData) {
    try {
      const orderId = await orderIdPromise;

      // Google Pay fires "INITIALIZE" once right after the sheet opens (using
      // the buyer's default address) in addition to "SHIPPING_ADDRESS" once
      // they actually pick a different one - both need the same shipping
      // options computed, otherwise the sheet never gets any to show
      if (intermediatePaymentData.callbackTrigger === "INITIALIZE" || intermediatePaymentData.callbackTrigger === "SHIPPING_ADDRESS") {
        const requestPayload = {
          id: orderId,
          purchase_units: [{ reference_id: "default" }],
          shipping_contact: googleAddressToContact(intermediatePaymentData.shippingAddress, ""),
        };

        const data = await $.ajax({
          type: "POST",
          url: getGoogleCartShippingMethodsUrl(),
          contentType: "application/json",
          dataType: "json",
          data: JSON.stringify(requestPayload),
        });

        const purchaseUnit = data.purchase_units[0];
        if (!purchaseUnit.shipping_options || purchaseUnit.shipping_options.length === 0) {
          return {
            error: {
              reason: "SHIPPING_ADDRESS_UNSERVICEABLE",
              message: "Cannot ship to the selected address",
              intent: "SHIPPING_ADDRESS",
            },
          };
        }

        return {
          newTransactionInfo: buildGoogleCartTransactionInfo(purchaseUnit.amount.value, "FINAL"),
          newShippingOptionParameters: {
            defaultSelectedOptionId: purchaseUnit.shipping_options[0].id,
            shippingOptions: purchaseUnit.shipping_options.map(function (option) {
              return { id: option.id, label: option.label, description: option.label };
            }),
          },
        };
      }

      if (intermediatePaymentData.callbackTrigger === "SHIPPING_OPTION") {
        const requestPayload = {
          id: orderId,
          purchase_units: [{ reference_id: "default" }],
          shipping_option: { id: intermediatePaymentData.shippingOptionData.id },
        };

        const data = await $.ajax({
          type: "POST",
          url: getGoogleCartShippingMethodsUrl(),
          contentType: "application/json",
          dataType: "json",
          data: JSON.stringify(requestPayload),
        });

        return {
          newTransactionInfo: buildGoogleCartTransactionInfo(data.purchase_units[0].amount.value, "FINAL"),
        };
      }

      return {};
    } catch (err) {
      reportGooglePayError("onPaymentDataChanged:" + intermediatePaymentData.callbackTrigger, err);
      return {
        error: {
          reason: "SHIPPING_ADDRESS_UNSERVICEABLE",
          message: "Unable to calculate shipping",
          intent: intermediatePaymentData.callbackTrigger,
        },
      };
    }
  };

  googlePaymentAuthorizedHandler = function (paymentData) {
    return new Promise(function (resolve) {
      orderIdPromise
        .then(function (orderId) {
          return processGoogleCartPayment(orderId, paymentData);
        })
        .then(function () {
          resolve({ transactionState: "SUCCESS" });
        })
        .catch(function (err) {
          reportGooglePayError("cartPaymentAuthorized", err);
          resolve({ transactionState: "ERROR" });
          // Let Google Pay process the ERROR result before leaving the page.
          setTimeout(redirectGoogleCartError, 0);
        });
    });
  };

  const button = paymentsClient.createButton({
    buttonColor: "default",
    buttonType: "buy",
    buttonLocale: getGoogleCartLocale(),
    onClick: function () {
      const { totalPrice, requiresShipping } = getGoogleCartTransactionInfo();

      orderIdPromise = $.post(getGoogleCartOrderUrl());

      const paymentDataRequest = Object.assign({}, baseRequest, {
        allowedPaymentMethods: cartPaymentMethods,
        merchantInfo,
        transactionInfo: buildGoogleCartTransactionInfo(totalPrice, "ESTIMATED"),
        emailRequired: true,
        callbackIntents: ["PAYMENT_AUTHORIZATION"],
      });

      if (requiresShipping) {
        paymentDataRequest.shippingAddressRequired = true;
        paymentDataRequest.shippingAddressParameters = { phoneNumberRequired: true };
        paymentDataRequest.shippingOptionRequired = true;
        paymentDataRequest.callbackIntents.push("SHIPPING_ADDRESS", "SHIPPING_OPTION");
      }

      paymentsClient.loadPaymentData(paymentDataRequest);
    },
  });

  document.getElementById("apms_button6").appendChild(button);
}

function buildGoogleCartTransactionInfo(totalPrice, status) {
  const { currencyIsoCode, totalLabel } = getGoogleCartTransactionInfo();
  return {
    currencyCode: currencyIsoCode,
    totalPriceStatus: status,
    totalPrice: String(totalPrice),
    totalPriceLabel: totalLabel,
  };
}

// maps Google Pay's address shape ({name, address1, address2,
// administrativeArea, locality, postalCode, countryCode, phoneNumber}) onto
// the same wallet contact shape Apple Pay delivers (givenName/familyName/
// addressLines/...), so the backend (parse_contact()) and the cart callback
// stay wallet-agnostic
function googleAddressToContact(address, email) {
  if (!address) {
    return null;
  }

  const nameParts = (address.name || "").trim().split(" ");
  const familyName = nameParts.length > 1 ? nameParts.pop() : "";
  const givenName = nameParts.join(" ");

  return {
    givenName: givenName,
    familyName: familyName,
    addressLines: [address.address1 || "", address.address2 || ""],
    administrativeArea: address.administrativeArea || "",
    locality: address.locality || "",
    postalCode: address.postalCode || "",
    countryCode: address.countryCode || "",
    phoneNumber: address.phoneNumber || "",
    emailAddress: email || "",
  };
}

async function processGoogleCartPayment(orderId, paymentData) {
  const billingAddress = paymentData.paymentMethodData.info.billingAddress;
  const shippingAddress = paymentData.shippingAddress || billingAddress;
  const email = paymentData.email || "";

  // store the wallet contact first: the patch step below reads it from the
  // session to attach the shipping address to the PayPal order, and the
  // success callback uses it to create the customer
  await $.post(getGoogleCartContactUrl(), {
    shippingContact: JSON.stringify(googleAddressToContact(shippingAddress, email)),
    billingContact: JSON.stringify(googleAddressToContact(billingAddress, email)),
  });

  // bring the PayPal order total in line with the shipping the buyer
  // selected in the sheet and attach the shipping address before confirming,
  // otherwise PayPal rejects with an APPROVE validation error
  const patchResult = await $.post(getGoogleCartPatchUrl());
  if (!patchResult || patchResult.success !== true) {
    throw new Error("Unable to update PayPal order");
  }

  const { status } = await paypal.Googlepay().confirmOrder({
    orderId: orderId,
    paymentMethodData: paymentData.paymentMethodData,
  });

  if (status === "PAYER_ACTION_REQUIRED") {
    await paypal.Googlepay().initiatePayerAction({ orderId: orderId });

    const orderResponse = await fetch(DIR_WS_BASE + "ajax.php?ext=check_paypal_order&payment_method=paypalgooglepay");
    if (!orderResponse.ok || (await orderResponse.json()) !== true) {
      throw new Error(orderResponse.ok ? "liability shift check failed" : "HTTP " + orderResponse.status);
    }
  }

  if (status !== "APPROVED" && status !== "PAYER_ACTION_REQUIRED") {
    throw new Error("Google Pay order not approved: " + status);
  }

  window.location.href = getGoogleCartSuccessUrl();
}

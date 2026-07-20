/* --------------------------------------------------------------
 $Id$

 modified eCommerce Shopsoftware
 http://www.modified-shop.org

 Copyright (c) 2009 - 2019 [www.modified-shop.org]
 --------------------------------------------------------------
 Released under the GNU General Public License
 --------------------------------------------------------------*/

// reports otherwise-invisible client-side Apple Pay failures to the server
function reportApplePayError(step, err) {
  try {
    var payload = new URLSearchParams({
      step: step,
      name: err && err.name ? err.name : "",
      message: err && err.message ? err.message : String(err),
    });
    if (navigator.sendBeacon) {
      navigator.sendBeacon(DIR_WS_BASE + "ajax.php?ext=log_paypal_client_error&payment_method=paypalapplepay", payload);
    } else {
      $.post(DIR_WS_BASE + "ajax.php?ext=log_paypal_client_error&payment_method=paypalapplepay", {
        step: step,
        name: err && err.name ? err.name : "",
        message: err && err.message ? err.message : String(err),
      });
    }
  } catch (e) {
    // reporting must never itself break the payment flow
  }
}

async function setupApplepay() {

  const { currencyIsoCode, totalPrice, totalPriceStatus, totalLabel, countryIsoCode } = getAppleTransactionInfo();
  const applepay = paypal.Applepay();
  const {
    isEligible,
    countryCode,
    currencyCode,
    merchantCapabilities,
    supportedNetworks,
  } = await applepay.config();

  if (!isEligible) {
    reportApplePayError("config", { message: "not eligible" });
    redirectAppleError();
  }

  document.getElementById("apms_button5").innerHTML = '<apple-pay-button id="btn-apple-pay" buttonstyle="black" type="buy" locale="' + countryIsoCode + '">';

  document.getElementById("btn-apple-pay").addEventListener("click", onClick);
  document.getElementsByClassName("apms_form_button_overlay")[0].style.display = 'none';

  async function onClick() {
    const { countryIsoCode, currencyIsoCode, totalPrice, totalPriceStatus, totalLabel } = getAppleTransactionInfo();

    const paymentRequest = {
      countryCode: countryIsoCode,
      currencyCode: currencyIsoCode,
      merchantCapabilities,
      supportedNetworks,
      requiredBillingContactFields: [
        "name",
        "email",
        "postalAddress",
      ],
      requiredShippingContactFields: [],
      total: {
        label: decodeAppleLabel(totalLabel),
        amount: totalPrice,
        type: totalPriceStatus,
      },
    };

    // eslint-disable-next-line no-undef
    let session = new ApplePaySession(4, paymentRequest);

    session.onvalidatemerchant = (event) => {
      applepay
        .validateMerchant({
          displayName: decodeAppleLabel(totalLabel),
          validationUrl: event.validationURL,
        })
        .then((payload) => {
          session.completeMerchantValidation(payload.merchantSession);
        })
        .catch((err) => {
          reportApplePayError("validateMerchant", err);
          session.abort();
        });
    };

    session.onpaymentmethodselected = (event) => {
      session.completePaymentMethodSelection({
        newTotal: paymentRequest.total,
      });
    };

    session.onpaymentauthorized = async (event) => {
      try {
        const id = getAppleOrderID();

        /**
         * Confirm Payment
         */
        await applepay.confirmOrder({
          orderId: id,
          token: event.payment.token,
          billingContact: event.payment.billingContact,
          shippingContact: event.payment.shippingContact
        });

        session.completePayment({
          status: window.ApplePaySession.STATUS_SUCCESS,
        });

        redirectAppleSuccess();
      } catch (err) {
        reportApplePayError("paymentAuthorized", err);
        session.completePayment({
          status: window.ApplePaySession.STATUS_FAILURE,
        });

        redirectAppleError();
      }
    };

    session.oncancel = (event) => {
      redirectAppleError();
    }

    session.begin();
  }
}


async function setupApplepayCart() {

  const applepay = paypal.Applepay();
  const {
    isEligible,
    countryCode,
    currencyCode,
    merchantCapabilities,
    supportedNetworks,
  } = await applepay.config();

  if (!isEligible) {
    reportApplePayError("cartConfig", { message: "not eligible" });
    return;
  }

  document.getElementById("apms_button5").innerHTML = '<apple-pay-button id="btn-apple-pay-cart" buttonstyle="black" type="buy" locale="' + countryCode + '">';
  document.getElementById("btn-apple-pay-cart").addEventListener("click", onClickCart);

  function onClickCart() {
    const { currencyIsoCode, totalPrice, totalLabel } = getAppleCartTransactionInfo();

    const paymentRequest = {
      countryCode,
      currencyCode: currencyIsoCode || currencyCode,
      merchantCapabilities,
      supportedNetworks,
      requiredBillingContactFields: [
        "name",
        "email",
        "postalAddress",
      ],
      requiredShippingContactFields: [
        "name",
        "email",
        "postalAddress",
      ],
      total: {
        label: decodeAppleLabel(totalLabel),
        amount: totalPrice,
        type: "pending",
      },
    };

    // ApplePaySession must be created synchronously, directly in the click
    // handler - any await before this point loses the user-gesture context
    // eslint-disable-next-line no-undef
    let session = new ApplePaySession(4, paymentRequest);

    // order creation can happen in parallel, it is only needed once the
    // shipping/payment callbacks below actually fire
    const orderIdPromise = $.post(getAppleCartOrderUrl());

    session.onvalidatemerchant = (event) => {
      applepay
        .validateMerchant({
          displayName: decodeAppleLabel(totalLabel),
          validationUrl: event.validationURL,
        })
        .then((payload) => {
          session.completeMerchantValidation(payload.merchantSession);
        })
        .catch((err) => {
          reportApplePayError("cartValidateMerchant", err);
          session.abort();
        });
    };

    session.onshippingcontactselected = (event) => {
      orderIdPromise.then((orderId) => $.ajax({
        type: "POST",
        url: getAppleCartShippingMethodsUrl(),
        contentType: "application/json",
        dataType: "json",
        data: JSON.stringify({
          id: orderId,
          purchase_units: [{ reference_id: "default" }],
          shipping_address: { country_code: event.shippingContact.countryCode },
        }),
      })).then((data) => {
        const purchaseUnit = data.purchase_units[0];
        session.completeShippingContactSelection({
          newShippingMethods: purchaseUnit.shipping_options.map((option) => ({
            label: option.label,
            detail: "",
            amount: option.amount.value,
            identifier: option.id,
          })),
          newTotal: {
            label: decodeAppleLabel(totalLabel),
            amount: purchaseUnit.amount.value,
            type: "final",
          },
          newLineItems: [],
        });
      }).catch((err) => {
        reportApplePayError("cartShippingContactSelected", err);
        session.completeShippingContactSelection({
          newShippingMethods: [],
          newTotal: paymentRequest.total,
          newLineItems: [],
        });
      });
    };

    session.onshippingmethodselected = (event) => {
      orderIdPromise.then((orderId) => $.ajax({
        type: "POST",
        url: getAppleCartShippingMethodsUrl(),
        contentType: "application/json",
        dataType: "json",
        data: JSON.stringify({
          id: orderId,
          purchase_units: [{ reference_id: "default" }],
          shipping_option: { id: event.shippingMethod.identifier },
        }),
      })).then((data) => {
        const purchaseUnit = data.purchase_units[0];
        session.completeShippingMethodSelection({
          newTotal: {
            label: decodeAppleLabel(totalLabel),
            amount: purchaseUnit.amount.value,
            type: "final",
          },
          newLineItems: [],
        });
      }).catch((err) => {
        reportApplePayError("cartShippingMethodSelected", err);
        session.completeShippingMethodSelection({
          newTotal: paymentRequest.total,
          newLineItems: [],
        });
      });
    };

    session.onpaymentauthorized = async (event) => {
      try {
        const orderId = await orderIdPromise;

        // store the Apple Pay contact first: the patch step below reads it from
        // the session to attach the shipping address to the PayPal order, and
        // the success callback uses it to create the customer
        await $.post(getAppleCartContactUrl(), {
          shippingContact: JSON.stringify(event.payment.shippingContact),
          billingContact: JSON.stringify(event.payment.billingContact),
        });

        // bring the PayPal order total in line with the shipping the buyer
        // selected in the sheet and attach the shipping address before confirming,
        // otherwise PayPal rejects with APPROVE_APPLE_PAY_VALIDATION_ERROR
        await $.post(getAppleCartPatchUrl());

        await applepay.confirmOrder({
          orderId,
          token: event.payment.token,
          billingContact: event.payment.billingContact,
          shippingContact: event.payment.shippingContact,
        });

        session.completePayment({
          status: window.ApplePaySession.STATUS_SUCCESS,
        });

        window.location.href = getAppleCartSuccessUrl();
      } catch (err) {
        reportApplePayError("cartPaymentAuthorized", err);
        session.completePayment({
          status: window.ApplePaySession.STATUS_FAILURE,
        });

        redirectAppleCartError();
      }
    };

    session.oncancel = (event) => { };

    session.begin();
  }
}


function decodeAppleLabel(str) {
  var element = document.createElement("div");

  if (str && typeof str === "string") {
    // strip script/html tags
    str = str.replace(/<script[^>]*>([\S\s]*?)<\/script>/gmi, '');
    str = str.replace(/<\/?\w(?:[^"'>]|"[^"]*"|'[^']*')*>/gmi, '');
    element.innerHTML = str;
    str = element.textContent;
    element.textContent = '';
  }

  return str;
}

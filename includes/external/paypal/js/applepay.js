/* --------------------------------------------------------------
 $Id$

 modified eCommerce Shopsoftware
 http://www.modified-shop.org

 Copyright (c) 2009 - 2019 [www.modified-shop.org]
 --------------------------------------------------------------
 Released under the GNU General Public License
 --------------------------------------------------------------*/

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
    redirectAppleError();
  }

  document.getElementById("apms_button3").innerHTML = '<apple-pay-button id="btn-apple-pay" buttonstyle="black" type="buy" locale="'+countryIsoCode+'">';

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
        "phone",
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
        await applepay.confirmOrder({ orderId: id, token: event.payment.token, billingContact: event.payment.billingContact , shippingContact: event.payment.shippingContact });

        session.completePayment({
          status: window.ApplePaySession.STATUS_SUCCESS,
        });
        
        redirectAppleSuccess();
      } catch (err) {
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


function decodeAppleLabel (str) {
  var element = document.createElement("div");

  if(str && typeof str === "string") {
    // strip script/html tags
    str = str.replace(/<script[^>]*>([\S\s]*?)<\/script>/gmi, '');
    str = str.replace(/<\/?\w(?:[^"'>]|"[^"]*"|'[^']*')*>/gmi, '');
    element.innerHTML = str;
    str = element.textContent;
    element.textContent = '';
  }

  return str;
}

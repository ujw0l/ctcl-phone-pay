class phonePayJs {

    constructor() {

        this.displayOrderDetail();
    }

    /**
     * Display order detail on link click
     */
    displayOrderDetail() {

        let payDetailLinks = document.querySelectorAll('.ctcl-get-phone-pay-data');
        if (0 < payDetailLinks.length) {

            Array.from(payDetailLinks).map(x => {
                x.addEventListener('click', e => {
                    let orderId = e.target.getAttribute('data-order-id');

                    var xhttp = new XMLHttpRequest();
                    xhttp.open('POST', ctclAdminObject.ajaxUrl, true);
                    xhttp.responseType = "text";
                    xhttp.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded;');
                    xhttp.addEventListener('load', event => {
                        if (event.target.status >= 200 && event.target.status < 400) {
                            new jsOverlay({ elContent: event.target.response, containerHt: 183, containerWd: 300, overlayNum: 1 });
                            this.markPhonePayPaid();
                        } else {
                            console.log(event.target.statusText);
                        }
                    });
                    xhttp.send(`action=getPhoneOrderDetail&orderId=${orderId}`);
                });
            });

        }
    }

    /**
     * Handle mark paid button clicked
     */
    markPhonePayPaid() {

        document.querySelector('.ctcl-pay-phone-mark-paid').addEventListener('click', () => {

            let orderId = document.querySelector('#ctcl-pay-phone-order-id').value;

            var xhttp = new XMLHttpRequest();
            xhttp.open('POST', ctclAdminObject.ajaxUrl, true);
            xhttp.responseType = "text";
            xhttp.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded;');
            xhttp.addEventListener('load', event => {
                if (event.target.status >= 200 && event.target.status < 400) {

                    console.log(event.target.response);

                    if ('success' == event.target.response) {
                        document.querySelector("#overlay-close-btn").click();
                        document.querySelector(`#ctcl-pending-phone-order-${orderId}`).parentElement.removeChild(document.querySelector(`#ctcl-pending-phone-order-${orderId}`));
                        alert(ctclPhonePayParams.markSucess);
                    } else {
                        alert(ctclPhonePayParams.markFail);
                    }

                } else {
                    console.log(event.target.statusText);
                }
            });
            xhttp.send(`action=phonePaymarkPaid&orderId=${orderId}`);
        })


    }



}

window.addEventListener('DOMContentLoaded', () => new phonePayJs());
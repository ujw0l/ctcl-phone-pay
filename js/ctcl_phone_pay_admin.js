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
                            new jsOverlay({ elContent: event.target.response, containerHt: 570, containerWd: 1080, overlayNum: 1 });

                        } else {
                            console.log(event.target.statusText);
                        }
                    });
                    xhttp.send(`action=getPhoneOrderDetail&orderId=${orderId}`);
                });
            });

        }
    }





}

window.addEventListener('DOMContentLoaded', () => new phonePayJs());
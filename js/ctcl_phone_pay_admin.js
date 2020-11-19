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
                            this.openOrderDetail();
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

    /**
     * Open order dteail modal
     */
    openOrderDetail() {
        document.querySelector('.ctcl-pay-phone-open-detail').addEventListener('click', () => {
            let orderId = document.querySelector('#ctcl-pay-phone-order-id').value;
            var xhttp = new XMLHttpRequest();
            xhttp.open('POST', ctclAdminObject.ajaxUrl, true);
            xhttp.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded;');
            xhttp.addEventListener('load', event => {

                if (event.target.status >= 200 && event.target.status < 400) {
                    document.querySelector('.js-overlay-close-1').click();
                    new jsOverlay({ elContent: event.target.response, containerHt: 600, containerWd: 1080, overlayNum: 2 });
                    new jsMasonry('.ctcl-pending-order-detail', { elWidth: 500, heightSort: 'desc', elMargin: 10 });
                    this.addPendingOrderModalEvent();
                } else {
                    console.log(event.target.statusText);
                }

            });

            xhttp.send(`action=pendingOrderDetail&orderId=${orderId}`);
        });

    }


    /**
     * All of the required event listener to be loaded after modal id loaded
     */
    addPendingOrderModalEvent() {
        let ctclMainAdminJs = new ctclAdminJs();
        ctclMainAdminJs.printOrderList();
        ctclMainAdminJs.printCustInfo();
        ctclMainAdminJs.vendorNoteSubmit();
        this.cancelPendingOrder();
        this.pendingOrderMarkComplete();
    }

    /**
     * Mark  order complete
     */

    pendingOrderMarkComplete() {

        document.querySelector('.ctcl-detail-mark-complete').addEventListener('click', e => {


            let orderId = document.querySelector('#ctcl-order-id').value;

            var xhttp = new XMLHttpRequest();
            xhttp.open('POST', ctclAdminObject.ajaxUrl, true);
            xhttp.responseType = "text";
            xhttp.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded;');
            xhttp.addEventListener('load', event => {
                if (event.target.status >= 200 && event.target.status < 400) {
                    let trToRemove = document.querySelector(`#ctcl-pending-phone-order-${orderId}`);
                    trToRemove.parentElement.removeChild(trToRemove);
                    document.querySelector('#overlay-close-btn').click();
                    alert(event.target.response);
                } else {
                    console.log(event.target.statusText);
                }
            })
            xhttp.send(`action=orderMarkComplete&orderId=${orderId}`);

        });


    }


    /**
     * Cancel order
     */
    cancelPendingOrder() {
        document.querySelector('.ctcl-detail-cancel-order').addEventListener('click', e => {
            let orderId = document.querySelector('#ctcl-order-id').value;
            if (confirm(ctclAdminObject.confirmCancelOrder)) {
                var xhttp = new XMLHttpRequest();
                xhttp.open('POST', ctclAdminObject.ajaxUrl, true);
                xhttp.responseType = "text";
                xhttp.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded;');
                xhttp.addEventListener('load', event => {
                    if (event.target.status >= 200 && event.target.status < 400) {
                        let trToRemove = document.querySelector(`#ctcl-pending-phone-order-${orderId}`);
                        trToRemove.parentElement.removeChild(trToRemove);
                        document.querySelector('#overlay-close-btn').click();
                        alert(event.target.response);
                    } else {
                        console.log(event.target.statusText);
                    }
                })
                xhttp.send(`action=cancelOrder&orderId=${orderId}`);
            }
        });
    }


}

window.addEventListener('DOMContentLoaded', () => new phonePayJs());
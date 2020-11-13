window.addEventListener('DOMContentLoaded', () => {

    Array.from(document.querySelectorAll('.ctcl-payment-option')).map(x => {
        x.addEventListener('change', e => {
            if (document.querySelector('#ctcl_phone_pay').checked) {
                document.querySelector('.ctcl-co-phone-number').required = true;
            } else {
                document.querySelector('.ctcl-co-phone-number').required = false;
            }
        });
    });
});
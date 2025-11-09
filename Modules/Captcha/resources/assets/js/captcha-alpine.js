document.addEventListener('alpine:init', () => {
    Alpine.data('captchaAlpine', () => ({
        message: '',
        captchaValue: '',

        init() {
            this.$watch('captchaValue', value => {
                if (value.length > 0) {
                    // Dispatch event for external handlers
                    window.dispatchEvent(new CustomEvent('captcha-input', {
                        detail: {value: value}
                    }));

                    // Call the callback via $dispatch
                    this.$dispatch('callback', value);
                }
            });
        },

        refreshCaptcha(imgElement) {


            // Parse the current URL to extract the id parameter
            const currentUrl = new URL(imgElement.src);
            const idParam = currentUrl.searchParams.get('id');

            // Get the base URL without query params
            const baseUrl = imgElement.src.split('?')[0];

            // Build new URL with fresh timestamp
            const timestamp = Date.now();
            const random = Math.random();
            let newUrl = `${baseUrl}?w=100&h=60&uid=${timestamp}&rand=${random}`;

            // Add the id parameter if it exists
            if (idParam) {
                newUrl += `&id=${idParam}`;
            }

            // Force reload by setting src
            imgElement.src = newUrl;

            // Clear the captcha input when refreshing
            this.captchaValue = '';

            console.log('Captcha refreshed:', newUrl);

            return false;
        }
    }));
});



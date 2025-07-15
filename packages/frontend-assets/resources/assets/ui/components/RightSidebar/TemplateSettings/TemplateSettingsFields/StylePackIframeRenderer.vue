<template>
    <div class="style-pack-iframe-container">
        <div v-if="isLoading" class="loading-overlay">
            <div class="spinner-container">
                <div class="spinner">
                    <div class="bounce1"></div>
                    <div class="bounce2"></div>
                    <div class="bounce3"></div>
                </div>
                <div class="loading-text">Loading styles...</div>
            </div>
        </div>
        
        <div ref="iframeContainer" class="iframe-wrapper"></div>
    </div>
</template>

<script>
export default {
    name: 'StylePackIframeRenderer',
    props: {
        stylePacks: {
            type: Array,
            required: true
        },
        selectorToApply: {
            type: String,
            default: ''
        },
        rootSelector: {
            type: String,
            default: ''
        },
        isExpanded: {
            type: Boolean,
            default: false
        },
        isStylePackOpenerMode: {
            type: Boolean,
            default: false
        },
        selectedStylePack: {
            type: Object,
            default: null
        },
        loadingIndex: {
            type: Number,
            default: null
        },
        previewElementsFormat: {
            type: String,
            default: 'block'
        },
        isDarkMode: {
            type: Boolean,
            default: false
        }
    },
    data() {
        return {
            iframe: null,
            isLoading: false,
            fontsLoaded: false,
            fontsToLoad: [],
            contentHash: null
        }
    },
    computed: {
        computedContentHash() {
            // Create a hash of the current content to avoid unnecessary re-renders
            return JSON.stringify({
                stylePacks: this.stylePacks,
                isExpanded: this.isExpanded,
                selectedStylePack: this.selectedStylePack,
                loadingIndex: this.loadingIndex,
                isDarkMode: this.isDarkMode
            });
        }
    },
    watch: {
        computedContentHash(newHash) {
            if (newHash !== this.contentHash) {
                this.contentHash = newHash;
                this.updateIframeContent();
            }
        },
        isDarkMode() {
            this.updateIframeContent();
        }
    },
    mounted() {
        console.log('StylePackIframeRenderer mounted with data:', {
            stylePacks: this.stylePacks,
            isExpanded: this.isExpanded,
            isStylePackOpenerMode: this.isStylePackOpenerMode,
            selectedStylePack: this.selectedStylePack
        });
        
        this.initializeIframe();
        this.scanAndLoadFonts();
    },
    beforeUnmount() {
        if (this.iframe) {
            this.iframe.remove();
        }
    },
    methods: {
        initializeIframe() {
            this.isLoading = true;
            
            // Create iframe element
            this.iframe = document.createElement('iframe');
            this.iframe.allowTransparency = true;
            this.iframe.loading = 'lazy';
            this.iframe.className = 'style-pack-preview-iframe';
            this.iframe.style.width = '100%';
            this.iframe.style.height = '400px';
            this.iframe.style.border = 'none';
            this.iframe.style.borderRadius = '7px';
            this.iframe.style.colorScheme = 'normal';

            // Append to container
            this.$refs.iframeContainer.appendChild(this.iframe);

            // Initialize iframe content after it's loaded
            this.iframe.onload = () => {
                this.isLoading = false;
                this.injectCanvasStyles();
                this.injectFontsIntoIframe();
                this.updateIframeContent();
                this.setupIframeAutoHeight();
            };

            // Set initial HTML structure
            this.iframe.srcdoc = this.getInitialHtml();
        },

        getInitialHtml() {
            const colors = this.getThemeColors();
            
            return `
                <!DOCTYPE html>
                <html>
                <head>
                    <meta charset="utf-8">
                    <title>Style Pack Preview</title>
                    <style>
                        ${this.getBaseStyles(colors)}
                    </style>
                </head>
                <body>
                    <div id="style-pack-container" class="style-pack-container ${this.isExpanded ? 'expanded' : ''}">
                        <!-- Content will be dynamically updated -->
                    </div>
                </body>
                </html>
            `;
        },

        getThemeColors() {
            const lightTheme = {
                borderColor: '#dee2e6',
                backgroundColor: '#f2f2f2',
                backgroundColorHover: '#d7d7d7',
                itemBackgroundColor: '#f8f9fa',
                textColor: '#495057',
                accentColor: '#007bff',
                shadowColor: 'rgba(0,0,0,0.1)'
            };

            const darkTheme = {
                borderColor: 'rgb(242, 242, 242)',
                backgroundColor: 'rgb(242, 242, 242)',
                backgroundColorHover: 'rgb(215, 215, 215)',
                itemBackgroundColor: '#1a202c',
                textColor: '#e2e8f0',
                accentColor: '#63b3ed',
                shadowColor: 'rgba(0,0,0,0.3)'
            };

            return this.isDarkMode ? darkTheme : lightTheme;
        },

        getBaseStyles(colors) {
            return `
                :root {
                    --border-color: ${colors.borderColor};
                    --background-color: ${colors.backgroundColor};
                    --background-color-hover: ${colors.backgroundColorHover};
                    --item-background-color: ${colors.itemBackgroundColor};
                    --text-color: ${colors.textColor};
                    --accent-color: ${colors.accentColor};
                    --shadow-color: ${colors.shadowColor};
                }

                body {
                    margin: 0;
                    padding: 0px;
                    background-color: transparent !important;
                    background: transparent !important;
                    color: var(--text-color);
                    overflow: hidden;
                    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
                }

                .style-pack-container {
                    display: flex;
                    flex-direction: column;
                    gap: 15px;
                }

                .style-pack-item {
                    cursor: pointer;
                    padding: 27px 22px 22px;
                    border-radius: 8px;
                    transition: all 0.2s;
                    border: 1px solid var(--border-color);
                    margin-bottom: 10px;
                    background-color: var(--background-color);
                    zoom: 87%;
                    position: relative;
                }

                .style-pack-item:hover {
                    box-shadow: 0 2px 4px var(--shadow-color);
                    background-color: var(--background-color-hover);
                }

                .style-pack-item.loading {
                    opacity: 0.6;
                    pointer-events: none;
                }

                .style-pack-item.loading::after {
                    content: '';
                    position: absolute;
                    top: 50%;
                    left: 50%;
                    width: 20px;
                    height: 20px;
                    border: 2px solid var(--accent-color);
                    border-top: 2px solid transparent;
                    border-radius: 50%;
                    animation: spin 1s linear infinite;
                    transform: translate(-50%, -50%);
                }

                @keyframes spin {
                    0% { transform: translate(-50%, -50%) rotate(0deg); }
                    100% { transform: translate(-50%, -50%) rotate(360deg); }
                }

                .style-pack-opener {
                    cursor: pointer;
                    padding: 27px 28px 22px;
                    border-radius: 8px;
                    transition: all 0.2s;
                    border: 1px solid var(--border-color);
                    margin-bottom: 10px;
                    background-color: var(--background-color);
                    position: relative;
                    zoom: 90%;
                }

                .style-pack-opener:after {
                    content: '>';
                    position: absolute;
                    right: 6px;
                    bottom: -8px;
                    transform: translateY(-50%);
                    font-size: 16px;
                    color: #000;
                    opacity: 0.7;
                    transition: all 0.3s ease;
                    padding: 5px;
                    width: 25px;
                    height: 25px;
                    background: white;
                    border-radius: 999px;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    border: 1px solid var(--border-color);
                }

                .style-pack-opener:hover:after {
                    transform: translateY(-50%) translateX(3px);
                    color: #007bff;
                    opacity: 1;
                    text-shadow: 0 0 5px rgba(0,123,255,0.3);
                    animation: arrow-pulse 1s infinite alternate;
                }

                @keyframes arrow-pulse {
                    0% { opacity: 0.7; }
                    100% { opacity: 1; }
                }

                .style-pack-container.expanded .style-pack-item {
                    display: block;
                }

                .style-pack-container:not(.expanded) .style-pack-item {
                    display: none;
                }

                .style-preview-item {
                    padding: 8px 15px;
                    border-radius: 6px;
                }

                .preview-display-block {
                    display: block;
                }

                .preview-display-flex {
                    display: flex;
                    flex-wrap: wrap;
                    gap: 10px;
                }

                .preview-display-flexZoom {
                    display: flex;
                    flex-wrap: wrap;
                    gap: 10px;
                    zoom: 0.8;
                }

                .style-preview-element {
                    min-height: 40px;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                }

                .preview-display-block .style-preview-element {
                    width: 100%;
                    margin-bottom: 10px;
                }

                .style-preview-element p, .preview-component {
                    margin: 0;
                    padding: 5px 10px;
                    background-color: var(--item-background-color);
                    border-radius: 4px;
                }

                .style-label {
                    font-size: 12px;
                    color: var(--text-color);
                    margin-top: 8px;
                    text-align: center;
                    font-weight: 500;
                }

                .d-flex {
                    display: flex;
                }

                .flex-column {
                    flex-direction: column;
                }
            `;
        },

        scanAndLoadFonts() {
            if (!this.stylePacks || this.stylePacks.length === 0) {
                return;
            }

            if (this.fontsLoaded && this.fontsToLoad.length > 0) {
                console.log('Fonts already loaded, skipping scan');
                return;
            }

            const fontFamilyProperties = [];
            const fontManager = window.mw?.top()?.app?.fontManager;

            if (!fontManager) {
                console.warn('Font manager not available');
                return;
            }

            // Scan all style packs for font-family properties
            this.stylePacks.forEach(stylePack => {
                if (!stylePack.properties) return;

                Object.entries(stylePack.properties).forEach(([key, value]) => {
                    if (key.endsWith('-font-family')) {
                        fontFamilyProperties.push(value);
                    }
                });
            });

            // Parse and load each font family
            this.fontsToLoad = [];
            fontFamilyProperties.forEach(fontFamilyStr => {
                if (fontManager.parseFontFamilies) {
                    const fontFamilies = fontManager.parseFontFamilies(fontFamilyStr);
                    fontFamilies.forEach(family => {
                        if (family && !this.fontsToLoad.includes(family)) {
                            if (fontManager.isGenericFontFamily(family)) {
                                return;
                            }
                            this.fontsToLoad.push(family);
                        }
                    });
                }
            });

            // Load each font
            this.fontsToLoad.forEach(family => {
                fontManager.loadNewFontTemp(family);
            });

            this.fontsLoaded = true;
        },

        injectFontsIntoIframe() {
            if (!this.iframe || !this.iframe.contentDocument || !this.fontsToLoad.length) return;

            const fontManager = window.mw?.top()?.app?.fontManager;
            if (!fontManager) return;

            const iframeDoc = this.iframe.contentDocument;
            const iframeHead = iframeDoc.head;

            this.fontsToLoad.forEach(family => {
                const fontUrl = fontManager.getFontUrl(family);
                if (!fontUrl) return;

                const fontId = 'font-' + family.replace(/[^a-zA-Z0-9]/g, '');
                
                // Skip if already added
                if (iframeDoc.getElementById(fontId)) return;

                const link = iframeDoc.createElement('link');
                link.id = fontId;
                link.rel = 'stylesheet';
                link.href = fontUrl;
                link.setAttribute("referrerpolicy", "no-referrer");
                link.setAttribute("crossorigin", "anonymous");
                link.setAttribute("data-noprefix", "1");
                
                iframeHead.appendChild(link);
            });
        },

        injectCanvasStyles() {
            if (!this.iframe || !this.iframe.contentDocument) return;

            try {
                const parentDoc = window.mw?.top()?.document;
                if (!parentDoc) return;

                const iframeDoc = this.iframe.contentDocument;
                const iframeHead = iframeDoc.head;

                // Inject canvas styles
                const canvasStyles = parentDoc.querySelectorAll('style[data-for-canvas], link[data-for-canvas]');
                canvasStyles.forEach(style => {
                    if (style.tagName === 'STYLE') {
                        const newStyle = iframeDoc.createElement('style');
                        newStyle.textContent = style.textContent;
                        newStyle.setAttribute('data-for-canvas', '1');
                        iframeHead.appendChild(newStyle);
                    } else if (style.tagName === 'LINK') {
                        const newLink = iframeDoc.createElement('link');
                        newLink.rel = style.rel;
                        newLink.href = style.href;
                        newLink.setAttribute('data-for-canvas', '1');
                        iframeHead.appendChild(newLink);
                    }
                });
            } catch (error) {
                console.warn('Could not inject canvas styles:', error);
            }
        },

        updateIframeContent() {
            if (!this.iframe || !this.iframe.contentDocument) {
                console.warn('Iframe not ready for content update');
                return;
            }

            const container = this.iframe.contentDocument.getElementById('style-pack-container');
            if (!container) {
                console.warn('Style pack container not found in iframe');
                return;
            }

            console.log('Updating iframe content with:', {
                stylePacks: this.stylePacks,
                isExpanded: this.isExpanded,
                isStylePackOpenerMode: this.isStylePackOpenerMode,
                selectedStylePack: this.selectedStylePack
            });

            // Update container class
            container.className = `style-pack-container ${this.isExpanded ? 'expanded' : ''}`;

            // Generate content
            let content = '';

            if (this.isStylePackOpenerMode && this.selectedStylePack) {
                // Show opener for selected style pack
                content = this.generateOpenerContent(this.selectedStylePack);
            } else if (this.isExpanded) {
                // Show all style packs
                content = this.generateStylePacksContent();
            } else {
                // Show opener for the first style pack or default
                const firstStylePack = this.stylePacks[0];
                content = this.generateOpenerContent(firstStylePack);
            }

            console.log('Generated content:', content);
            container.innerHTML = content;
            this.attachEventListeners();
        },

        generateOpenerContent(stylePack) {
            if (!stylePack) return '';

            const isLoading = this.loadingIndex === 0;
            const loadingClass = isLoading ? 'loading' : '';

            return `
                <div class="style-pack-opener ${loadingClass}" data-style-pack-index="opener">
                    <div class="style-preview-item preview-display-${this.previewElementsFormat}">
                        ${this.generatePreviewElements(stylePack)}
                    </div>
                    ${stylePack.label ? `<div class="style-label">${stylePack.label}</div>` : ''}
                </div>
            `;
        },

        generateStylePacksContent() {
            return this.stylePacks.map((stylePack, index) => {
                const isLoading = this.loadingIndex === index;
                const loadingClass = isLoading ? 'loading' : '';

                return `
                    <div class="style-pack-item ${loadingClass}" data-style-pack-index="${index}">
                        <div class="style-preview-item preview-display-${this.previewElementsFormat}">
                            ${this.generatePreviewElements(stylePack)}
                        </div>
                        ${stylePack.label ? `<div class="style-label">${stylePack.label}</div>` : ''}
                    </div>
                `;
            }).join('');
        },

        generatePreviewElements(stylePack) {
            if (!stylePack.properties) return '<div class="style-preview-element">No preview available</div>';

            // Check if stylePack has previewElements defined
            if (stylePack.previewElements && Array.isArray(stylePack.previewElements)) {
                return stylePack.previewElements.map(element => {
                    const styles = Object.entries(stylePack.properties)
                        .map(([prop, value]) => `${prop.replace(/([A-Z])/g, '-$1').toLowerCase()}: ${value}`)
                        .join('; ');
                    
                    return `<div class="style-preview-element" style="${styles}">${element}</div>`;
                }).join('');
            }

            // Generate preview elements based on the style pack properties
            const elements = [];
            
            // Create sample elements to show the style
            const sampleElements = [
                '<p>Sample text</p>',
                '<div class="preview-component">Component</div>',
                '<span class="preview-component">Element</span>'
            ];

            sampleElements.forEach(element => {
                const styles = Object.entries(stylePack.properties)
                    .map(([prop, value]) => `${prop.replace(/([A-Z])/g, '-$1').toLowerCase()}: ${value}`)
                    .join('; ');
                
                elements.push(`
                    <div class="style-preview-element" style="${styles}">
                        ${element}
                    </div>
                `);
            });

            return elements.join('');
        },

        attachEventListeners() {
            if (!this.iframe || !this.iframe.contentDocument) return;

            const items = this.iframe.contentDocument.querySelectorAll('[data-style-pack-index]');
            items.forEach(item => {
                item.addEventListener('click', (e) => {
                    const index = e.currentTarget.getAttribute('data-style-pack-index');
                    
                    if (index === 'opener') {
                        this.$emit('opener-clicked');
                    } else {
                        const stylePackIndex = parseInt(index);
                        const stylePack = this.stylePacks[stylePackIndex];
                        if (stylePack) {
                            this.$emit('style-pack-selected', { stylePack, index: stylePackIndex });
                        }
                    }
                });
            });
        },

        setupIframeAutoHeight() {
            if (window.mw?.top()?.tools?.iframeAutoHeight) {
                window.mw.top().tools.iframeAutoHeight(this.iframe);
            }
        }
    }
};
</script>

<style scoped>
.style-pack-iframe-container {
    position: relative;
}

.loading-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: rgba(255, 255, 255, 0.8);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 10;
    border-radius: 7px;
}

.spinner-container {
    text-align: center;
}

.loading-text {
    margin-top: 15px;
    color: #007bff;
    font-size: 14px;
    font-weight: 500;
}

.spinner {
    margin: 0 auto;
    width: 70px;
    text-align: center;
}

.spinner > div {
    width: 12px;
    height: 12px;
    background-color: #007bff;
    border-radius: 100%;
    display: inline-block;
    animation: sk-bouncedelay 1.4s infinite ease-in-out both;
    margin: 0 3px;
}

.spinner .bounce1 {
    animation-delay: -0.32s;
}

.spinner .bounce2 {
    animation-delay: -0.16s;
}

@keyframes sk-bouncedelay {
    0%, 80%, 100% {
        transform: scale(0);
    }
    40% {
        transform: scale(1.0);
    }
}

.iframe-wrapper {
    width: 100%;
    min-height: 100px;
}
</style>

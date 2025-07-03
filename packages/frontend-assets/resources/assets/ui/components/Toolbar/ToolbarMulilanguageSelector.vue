<template>
    <div class="mw-live-edit-right-sidebar-wrapper mx-2" v-if="isReady">
        <div class="custom-dropdown">
            <a role="button" class="dropdown-trigger"
               :aria-expanded="dropdownOpen.toString()" @click.prevent="toggleDropdown">
                <span :class="flagClass"></span>
                {{ currentLanguage }}
            </a>

            <ul class="dropdown-content" :class="{ 'show': dropdownOpen }"
                ref="multilanguageSwticherSettingsDropdown">
                <li v-for="(language,locale) in languages" :key="locale">
                    <a @click="changeLang(locale)" :class="{ active: currentLanguage == locale }">
                        <span :class="'flag-icon flag-icon-' + languagesIcons[locale]"></span>
                        {{ language }}
                    </a>
                </li>

                <li class="settings-item">
                    <a @click="showLangSettings">
                        <span class="mdi mdi-cog"></span>
                        Settings
                    </a>
                </li>
            </ul>
        </div>
    </div>
</template>

<style>
.custom-dropdown {
    position: relative;
    display: inline-block;
}

.dropdown-trigger {
    cursor: pointer;
    padding: 8px;
    display: flex;
    align-items: center;
    gap: 5px;
}

.dropdown-content {
    display: none;
    position: absolute;
    top: 100%;
    right: 0;
    background-color: #fff;
    min-width: 160px;
    box-shadow: 0 2px 5px rgba(0,0,0,0.2);
    border-radius: 4px;
    padding: 5px 0;
    z-index: 1000;
}

.dropdown-content.show {
    display: block;
}

.dropdown-content li {
    list-style: none;
}

.dropdown-content li a {
    display: flex;
    align-items: center;
    padding: 8px 16px;
    text-decoration: none;
    color: #333;
    cursor: pointer;
}

.dropdown-content li a:hover {
    background-color: #f5f5f5;
}

.dropdown-content li a.active {
    background-color: #e9ecef;
}

.flag-icon {
    margin-right: 7px;
}

.settings-item {
    border-top: 1px solid #eee;
    margin-top: 5px;
    padding-top: 5px;
}
</style>

<script>


export default {
    data() {
        return {
            isReady: false,
            languages: {},
            languagesIcons: {},
            currentLanguage: false,
            dropdownOpen: false
        }
    },
    computed: {
        flagClass() {
            return 'flag-icon flag-icon-' + this.languagesIcons[this.currentLanguage];
        }
    },
    components: {},
    methods: {
        toggleDropdown() {
            if (this.$refs.multilanguageSwticherSettingsDropdown && this.$refs.multilanguageSwticherSettingsDropdown.classList) {
                this.$refs.multilanguageSwticherSettingsDropdown.classList.toggle('show');
                this.dropdownOpen = !this.dropdownOpen;
            }
        },
        hideLangDropdown() {
            if (this.$refs.multilanguageSwticherSettingsDropdown && this.$refs.multilanguageSwticherSettingsDropdown.classList) {
                this.$refs.multilanguageSwticherSettingsDropdown.classList.remove('show');
                this.dropdownOpen = false;
            }
        },
        showLangSettings() {
            if (this.$refs.multilanguageSwticherSettingsDropdown && this.$refs.multilanguageSwticherSettingsDropdown.classList) {
                this.$refs.multilanguageSwticherSettingsDropdown.classList.remove('show');
                this.dropdownOpen = false;
            }
             // go to admin settings page

            var url = mw.settings.adminUrl + 'multilanguage-settings-admin';

            //open in new tab

           window.open(url, '_blank');

            // or you can use mw.url.open(url);
            // mw.url.open(url);


        },


        changeLang: function (name) {
            var from_url = mw.app.canvas.getDocument().location.href;
            $.post(mw.settings.api_url + "multilanguage/change_language", {
                locale: name,
                from_url: from_url,


            })
                .done(function (data) {
                    if (data.refresh) {
                        if (data.location) {
                            mw.app.canvas.getDocument().location.href = data.location;
                        } else {
                            mw.app.canvas.getDocument().location.reload();
                        }
                    }

                });

        }
    },

    mounted() {

        mw.app.canvas.on('canvasDocumentClick', event => {
            this.hideLangDropdown();

        });

        mw.app.canvas.on('liveEditCanvasBeforeUnload', event => {
            this.hideLangDropdown();
        });


        mw.app.on('populateSupportedLanguages', data => {


            if (!Array.isArray(data)) {
                return;
            }
            this.languages = {};
            data.forEach((item, index) => {
                this.languages[item.locale] = item.language;
                this.languagesIcons[item.locale] = item.icon;
            })

        });


        mw.app.canvas.on('liveEditCanvasLoaded', () => {


            var liveEditIframeData = mw.app.canvas.getLiveEditData();

            if (liveEditIframeData
                && liveEditIframeData.content
                && liveEditIframeData.content.id
                && liveEditIframeData.content.title
                && liveEditIframeData.multiLanguageIsEnabled
                && liveEditIframeData.multiLanguageCurrentLanguage
                && liveEditIframeData.multiLanguageEnabledLanguages
                && liveEditIframeData.multiLanguageEnabledLanguages.length > 0
                && liveEditIframeData.multiLanguageIsEnabled == 1
            ) {
                var cont_id = liveEditIframeData.content.id;


                liveEditIframeData.multiLanguageEnabledLanguages.forEach((item, index) => {
                    this.languages[item.locale] = item.language;
                    this.languagesIcons[item.locale] = item.icon;
                })
                this.currentLanguage = liveEditIframeData.multiLanguageCurrentLanguage;

                this.isReady = true;
            }


        })
    }
}
</script>

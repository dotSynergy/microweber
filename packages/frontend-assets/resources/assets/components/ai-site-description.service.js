import axios from 'axios'

const saveSiteTitle = async (title) => {
    try {
        const response = await axios.post(mw.settings.api_url + 'save_option', {
            option_key: 'website_title',
            option_group: 'website',
            option_value: title
        }, {
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || mw.cookie.get("XSRF-TOKEN")
            }
        })

        if (response.data.is_saved) {
            console.log('Site title saved successfully')
        }
    } catch (error) {
        console.error('Error saving site title:', error)
    }
}

const saveSiteDescription = async (description) => {
    try {
        const response = await axios.post(mw.settings.api_url + 'save_option', {
            option_key: 'website_description',
            option_group: 'website',
            option_value: description
        }, {
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || mw.cookie.get("XSRF-TOKEN")
            }
        })

        if (response.data.is_saved) {
            console.log('Site description saved successfully')
        }
    } catch (error) {
        console.error('Error saving site description:', error)
    }
}

const saveSiteKeywords = async (keywords) => {
    try {
        const response = await axios.post(mw.settings.api_url + 'save_option', {
            option_key: 'website_keywords',
            option_group: 'website',
            option_value: keywords
        }, {
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || mw.cookie.get("XSRF-TOKEN")
            }
        })

        if (response.data.is_saved) {
            console.log('Site keywords saved successfully')
        }
    } catch (error) {
        console.error('Error saving site keywords:', error)
    }
}

export const generateSiteInfoWithAI = async (prompt) => {
    prompt = (prompt || '').trim();
    if (!prompt) {

        return
    }


    try {



        // Emit AI request start event


        const message = `Based on this website description: "${prompt}"

Please generate appropriate website information in JSON format with the following structure:
{
    "title": "website title (max 100 characters)",
    "description": "SEO description (max 160 characters)",
    "keywords": "relevant keywords separated by commas (max 200 characters)"
}

Make sure the content is relevant, professional, and optimized for SEO.`

        const messages = [{role: 'user', content: message}]


        const response = await window.mw.top().win.MwAi().sendToChat(messages, {
            schema: JSON.stringify({
                title: "",
                description: "",
                keywords: ""
            })
        })

        if (response.success && response.data) {
            // Populate the fields with AI-generated content
            if (response.data.title) {


                await saveSiteTitle(response.data.title)
            }

            if (response.data.description) {


                await saveSiteDescription(response.data.description)
            }

            if (response.data.keywords) {


                await saveSiteKeywords(response.data.keywords)
            }


        } else {
            throw new Error('Invalid response from AI')
        }
    } catch (error) {
        console.error('AI generation error:', error)

    } finally {

    }
}

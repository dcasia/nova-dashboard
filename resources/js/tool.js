import Tool from './components/Tool'
import SocialMediaWidget from './widgets/SocialMediaWidget'

Nova.booting((Vue, router, store) => {

    router.addRoutes([
        {
            name: 'nova-bi',
            path: '/nova-bi/:resource',
            component: Tool
        }
    ])

    Vue.component('social-media-widget', SocialMediaWidget)

})


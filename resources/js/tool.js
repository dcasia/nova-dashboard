import NovaWidget from './components/NovaWidget'
import Widget from './components/Widget'

Nova.booting((Vue, router, store) => {

    router.addRoutes([
        {
            name: 'nova-bi',
            path: '/nova-bi/:resource',
            component: NovaWidget
        }
    ])

    Vue.component('widget', Widget)

})


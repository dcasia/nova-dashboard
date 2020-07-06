import NovaWidget from './components/NovaWidget'
import Widget from './components/Widget'

Nova.booting((Vue, router, store) => {

    router.addRoutes([
        {
            name: 'nova-widgets',
            path: '/nova-widgets/:resource',
            component: NovaWidget
        }
    ])

    Vue.component('widget', Widget)

})


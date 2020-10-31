import Dashboard from './components/Dashboard'
import Widget from './components/Widget'
import VueHtml2Canvas from 'vue-html2canvas';


Nova.booting((Vue, router, store) => {

    router.addRoutes([
        {
            name: 'nova-dashboard',
            path: '/nova-dashboard/:dashboardKey',
            component: Dashboard
        }
    ])

    Vue.component('widget', Widget)
    Vue.use(VueHtml2Canvas);
})


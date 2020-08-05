import { castValuesToNativeTypes, dotter } from './helpers'

export default {
    namespaced: true,
    state: () => ( {
        options: {},
        widgetData: {}
    } ),
    getters: {
        rawOptions: state => state.options,
        options: state => dotter.object({ ...state.options }),
        widgetData: state => state.widgetData
    },
    mutations: {
        updateOptions(state, options) {
            state.options = castValuesToNativeTypes(options)
        },
        updateWidgetData(state, data) {
            state.widgetData = data
        }
    }
}

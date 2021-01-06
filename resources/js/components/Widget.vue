<template>

    <loading-card-overlay :loading="loading"
                  class="flex flex-col w-full rounded justify-between"
                  :class="{ 'px-6 py-4': !noPadding }">

        <FadeTransition>

            <slot v-if="loading === false || preloaded"
                  :options="options"
                  :value="value"
                  :namespace="namespace" />

        </FadeTransition>

    </loading-card-overlay>

</template>

<script>

import { Minimum } from 'laravel-nova'
import { FadeTransition } from 'vue2-transitions'
import store from '../store'

export default {
    name: 'Widget',
    components: { FadeTransition },
    props: {
        meta: { type: Object, required: true },
        card: { type: Object, required: true },
        noPadding: { type: Boolean, default: false }
    },
    data() {
        return {
            loading: true,
            preloaded: false
        }
    },
    computed: {
        namespace() {
            return [ this.meta.dashboardKey, this.meta.viewKey, this.meta.widgetKey, this.meta.id ].join('/')
        },
        value() {
            return this.$store.getters[ `${ this.namespace }/widgetData` ]
        },
        options() {
            return this.$store.getters[ `${ this.namespace }/options` ]
        }
    },
    async created() {

        const callback = encodedFilters => this.fetchData(encodedFilters)

        Nova.$on('NovaFilterUpdate', callback)
        Nova.$on(`widget-${ this.meta.id }-updated`, callback)

        this.$on('hook:destroyed', () => {
            Nova.$off('NovaFilterUpdate', callback)
            Nova.$off(`widget-${ this.meta.id }-updated`, callback)
        })

    },
    async mounted() {

        /**
         * Initialize Vuex
         */
        this.$store.registerModule(this.namespace, store)
        this.$store.commit(`${ this.namespace }/updateOptions`, this.meta.options)

        await this.fetchData()

    },
    beforeDestroy() {
        this.$store.unregisterModule(this.namespace)
    },
    methods: {
        async fetchData() {

            this.loading = true

            const encodedFilters = this.$store.getters[ `${ this.meta.dashboardKey }/currentEncodedFilters` ]
            const options = this.$store.getters[ `${ this.namespace }/options` ]

            await Minimum(
                Nova.request({
                    method: 'post',
                    url: '/nova-vendor/nova-dashboard/fetch-widget-data',
                    data: {
                        editMode: 'create',
                        editing: true,
                        dashboard: this.meta.dashboardKey,
                        view: this.meta.viewKey,
                        widget: this.meta.widgetKey,
                        filters: encodedFilters,
                        options: this.meta.editable ? options : undefined
                    }
                }), 300
            ).then(response => {

                this.$store.commit(`${ this.namespace }/updateWidgetData`, response.data)

                this.$nextTick(() => this.loading = false)
                
                this.$nextTick(() => this.preloaded = true)

            }).catch(error => {

                try {

                    Nova.error(error.response.data.message)

                } catch {

                    Nova.error(this.__('Failed to load :widget.', { 'widget': widget.widgetKey }))

                }

            })

        }

    }

}

</script>

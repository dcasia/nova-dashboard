<template>

    <loading-card :loading="loading"
                  class="flex flex-col w-full rounded justify-between"
                  :class="{ 'px-6 py-4': !noPadding }">

        <FadeTransition>
            <slot v-if="loading === false" v-bind="$data"/>
        </FadeTransition>

    </loading-card>

</template>

<script>

    import { Minimum } from 'laravel-nova'
    import { FadeTransition } from 'vue2-transitions'

    export default {
        name: 'Widget',
        components: { FadeTransition },
        props: {
            meta: { type: Object, required: true },
            noPadding: { type: Boolean, default: false }
        },
        data() {
            return {
                loading: true,
                value: null
            }
        },
        async created() {

            const callback = widget => this.fetchData(widget)

            Nova.$on('NovaFilterUpdate', callback)
            Nova.$on(`widget-${ this.meta.id }-updated`, callback)

            this.$on('hook:destroyed', () => {
                Nova.$off('NovaFilterUpdate', callback)
                Nova.$off(`widget-${ this.meta.id }-updated`, callback)
            })

        },
        async mounted() {

            await this.fetchData(this.meta)

        },
        methods: {
            async fetchData(widget) {

                this.loading = true

                const filters = this.$store.getters[ `${ widget.dashboardKey }/currentEncodedFilters` ]

                await Minimum(
                    Nova.request({
                        method: 'post',
                        url: '/nova-vendor/nova-widgets/fetch-widget-data',
                        data: {
                            dashboard: widget.dashboardKey,
                            view: widget.viewKey,
                            widget: widget.widgetKey,
                            filters: filters,
                            options: widget.options
                        }
                    }), 300
                ).then(response => {

                    this.value = response.data

                    this.$nextTick(() => this.loading = false)

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

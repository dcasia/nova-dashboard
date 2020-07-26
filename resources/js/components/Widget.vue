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

            const callback = encodedFilters => this.fetchData(encodedFilters)

            Nova.$on('NovaFilterUpdate', callback)
            Nova.$on(`widget-${ this.meta.id }-updated`, callback)

            this.$on('hook:destroyed', () => {
                Nova.$off('NovaFilterUpdate', callback)
                Nova.$off(`widget-${ this.meta.id }-updated`, callback)
            })

        },
        async mounted() {

            await this.fetchData()

        },
        methods: {
            async fetchData() {

                this.loading = true

                const encodedFilters = this.$store.getters[ `${ this.meta.dashboardKey }/currentEncodedFilters` ]

                await Minimum(
                    Nova.request({
                        method: 'post',
                        url: '/nova-vendor/nova-dashboard/fetch-widget-data',
                        data: {
                            dashboard: this.meta.dashboardKey,
                            view: this.meta.viewKey,
                            widget: this.meta.widgetKey,
                            filters: encodedFilters,
                            options: this.meta.options
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

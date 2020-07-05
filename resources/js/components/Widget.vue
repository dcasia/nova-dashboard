<template>

    <loading-card :loading="loading" class="flex flex-col w-full rounded justify-between">
        <slot v-bind="$data"/>
    </loading-card>

</template>

<script>

    import { Minimum } from 'laravel-nova'

    export default {
        name: 'Widget',
        props: [ 'meta' ],
        data() {
            return {
                loading: true,
                value: null
            }
        },
        async created() {

            const callback = encodedFilters => this.fetchData(encodedFilters)

            Nova.$on('NovaFilterUpdate', callback)
            Nova.$on(`widget-${ this.meta.id }-update`, callback)

            this.$on('hook:destroyed', () => {
                Nova.$off('NovaFilterUpdate', callback)
                Nova.$off(`widget-${ this.meta.id }-update`, callback)
            })

            await this.fetchData()

        },
        methods: {
            async fetchData(encodedFilters = null) {

                this.loading = true

                const response = await Minimum(
                    Nova.request().post(`/nova-vendor/nova-bi/${ this.meta.uri }/${ this.meta.key }`, {
                        filters: encodedFilters,
                        options: this.meta.options
                    }), 300
                )

                this.value = response.data
                this.loading = false

            }
        }
    }

</script>

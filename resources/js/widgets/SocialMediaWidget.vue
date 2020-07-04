<template>

    <loading-card :loading="loading"
                  class="social-media-widget flex flex-col w-full rounded justify-between">

        <div :style="{ backgroundColor: icon.color }"
             class="flex flex-1 justify-center items-center h-full relative rounded-t-lg flex-1">

            <component :is="icon.component" class="text-white absolute"/>

            <TestChart class="absolute w-full h-full"/>

        </div>

        <div style="background: #23242d" class="flex text-white justify-center rounded-b-lg p-2" :class="{ 'p-4': meta.h > 1 }">

            <template v-for="([ content, label ], index) of value">

                <div v-if="index > 0" class="social-media-widget__divider border-r mx-2"></div>

                <div :key="index" class="flex text-center flex-col p-1">
                    <div class="font-normal text-2xl leading-normal">{{ content }}</div>
                    <div class="text-xs uppercase">{{ label }}</div>
                </div>

            </template>

        </div>

    </loading-card>

</template>

<script>

    import Facebook from './icons/Facebook'
    import Twitter from './icons/Twitter'
    import { Minimum } from 'laravel-nova'
    import TestChart from './TestChart'

    const socialMedias = {
        facebook: {
            component: Facebook,
            color: '#3b5998'
        },
        twitter: {
            component: Twitter,
            color: '#00acee'
        }
    }

    export default {
        components: { Facebook, TestChart },
        props: [ 'meta' ],
        data() {
            console.log(this.meta)
            return {
                socialMedias,
                loading: true,
                value: []
            }
        },
        async created() {

            const callback = encodedFilters => this.fetchData(encodedFilters)

            Nova.$on('NovaFilterUpdate', callback)

            this.$on('hook:destroyed', () => Nova.$off('NovaFilterUpdate', callback))

            await this.fetchData()

        },
        methods: {
            async fetchData(encodedFilters = null) {

                this.loading = true

                const response = await Minimum(
                    Nova.request(`/nova-vendor/nova-bi/${ this.meta.uri }/${ this.meta.id }?filters=${ encodedFilters }`), 300
                )

                this.value = response.data
                this.loading = false

            }
        },
        computed: {
            icon() {
                return socialMedias[ this.meta.data.type ]
            }
        }
    }

</script>


<style>

    .social-media-widget__divider {
        border-color: rgba(0, 0, 21, .2);
    }

</style>

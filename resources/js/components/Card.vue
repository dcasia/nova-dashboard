<template>

    <div class="nova-dashboard min-h-[auto] pt-0">

        <Filter
            v-if="activeView"
            :lens="lens"
            :views="card.views"
            :active-view="activeView"
            :resource="resourceName"
            :resource-name="activeView.key"
            :columns="card.columns"
            :via-resource="viaResource"
            :via-resource-id="viaResourceId"
            :via-relationship="viaRelationship"
            @toggle="onViewChange"/>

        <!--        <Cards v-if="activeView && activeView.widgets.length" :cards="activeView.widgets"/>-->

        <!--        <section class="grid-stack">-->

        <!--            <div v-for="card in activeView.widgets"-->
        <!--                 :key="`${card.component}.${card.uriKey}`"-->
        <!--                 :gs-id="`${card.component}.${card.uriKey}`"-->
        <!--                 class="grid-stack-item"-->
        <!--                 :gs-x="card.grid.x"-->
        <!--                 :gs-y="card.grid.y"-->
        <!--                 :gs-h="card.grid.h"-->
        <!--                 :gs-w="card.grid.w"-->
        <!--                 gs-auto-position="true">-->

        <!--                <div class="grid-stack-item-content h-full">-->

        <!--                    <component-->
        <!--                        :key="`${card.component}.${card.uriKey}`"-->
        <!--                        class="h-full"-->
        <!--                        :is="card.component"-->
        <!--                        :card="card"-->
        <!--                        :resource="resource"-->
        <!--                        :resourceName="resourceName"-->
        <!--                        :resourceId="resourceId"-->
        <!--                        :lens="lens"-->
        <!--                    />-->

        <!--                </div>-->

        <!--            </div>-->

        <!--        </section>-->

        <div class="flex justify-center">

            <div class="grid-stack h-full w-full">
                <Widget v-for="widget in activeView.widgets" :widget="widget" :key="widget.key"/>
            </div>

        </div>


    </div>

</template>

<script>

    import Filter from './Filter.vue'
    import resourceStore from '@/store/resources'
    import { GridStack } from 'gridstack'
    import Widget from './Widget.vue'

    export default {
        components: { Filter, Widget },
        props: [
            'card',
            'lens',
            'resourceName',
            'viaResource',
            'viaResourceId',
            'viaRelationship',
        ],
        data() {

            const key = Nova.store.getters[ 'queryStringParams' ].view
            const view = this.card.views.find(view => view.key === key)

            return {
                activeView: view ?? this.card.views[ 0 ],
            }

        },
        mounted() {

            const margin = 10

            var grid = GridStack.init({
                // float: true,
                cellHeight: 160 + margin * 2,
                margin: margin,
                auto: false,


                acceptWidgets: true,
            })

            this.$nextTick(() => {

                for (const widget of this.activeView.widgets) {
                    grid.makeWidget(`#${ widget.key }`)
                }

            })

        },
        created() {

            for (const view of this.card.views) {

                for (const widget of view.widgets) {
                    widget.view = view.key
                }

                this.$store.registerModule(view.key, resourceStore)
                this.$store.commit(`${ view.key }/storeFilters`, view.filters)

            }

        },
        methods: {
            onViewChange(view) {
                this.activeView = view
            },
        },
    }

</script>

<style lang="scss">

    div[dusk="loading-view"] {
        @apply min-h-[auto] #{!important};
    }

</style>

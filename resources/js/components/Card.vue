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

        <div class="flex justify-center w-[calc(100%+24px)] -ml-[12px]">

            <div ref="gridStack" class="grid-stack h-full w-full">
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
                grid: null,
                activeView: view ?? this.card.views[ 0 ],
            }

        },
        mounted() {
            this.createGrid()
        },
        unmounted() {
            this.destroyGrid()
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
        watch: {
            activeView() {
                this.createGrid()
            },
        },
        computed: {
            cacheKey() {
                return `${ this.activeView.key }-widgets`
            },
        },
        methods: {
            destroyGrid() {

                if (this.grid) {
                    this.grid.offAll()
                    this.grid.destroy(false)
                    this.$refs.gridStack?.removeAttribute('gs-static')
                }

            },
            createGrid() {

                this.destroyGrid()

                const margin = 12

                this.grid = GridStack.init({
                    staticGrid: this.activeView.static,
                    cellHeight: 160 + margin * 2,
                    margin: margin,
                    animate: false,
                    auto: false,
                })

                this.$nextTick(() => {

                    const savedWidgets = this.loadGrid()

                    for (const widget of this.activeView.widgets) {

                        const savedWidget = savedWidgets.find(item => item.id === widget.key)
                        const layout = this.activeView.static === true
                            ? widget
                            : savedWidget ?? widget

                        this.grid.makeWidget(`#${ widget.key }`, {
                            autoPosition: false,
                            id: widget.key,
                            minW: widget.minWidth,
                            minH: widget.minHeight,
                            x: layout.x,
                            y: layout.y,
                            w: layout.width,
                            h: layout.height,
                        })

                    }

                    this.grid.setAnimation(true)

                    this.grid.on('drag', () => this.saveGrid())
                    this.grid.on('resize', () => this.saveGrid())

                })

            },
            loadGrid() {
                try {
                    return JSON.parse(localStorage.getItem(this.cacheKey)) ?? []
                } catch {
                    return []
                }
            },
            saveGrid() {

                const nodes = this.grid.engine.nodes.map(node => ({
                    id: node.id,
                    width: node.w,
                    height: node.h,
                    x: node.x,
                    y: node.y,
                }))

                localStorage.setItem(this.cacheKey, JSON.stringify(nodes))

            },
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

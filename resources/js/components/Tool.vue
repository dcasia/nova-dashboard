<template>

    <div class="nova-bi">

        <button class="btn btn-default btn-primary" @click="closeModal = false">
            Add Widget
        </button>

        <component class="flex flex-col inline-flex"
                   v-for="filter in filters"
                   :key="filter.name"
                   :resource-name="resourceName"
                   :filter-key="filter.class"
                   :is="filter.component"
                   @input="filterChanged"
                   @change="filterChanged"/>

        <!--        <designer v-if="editMode" ref="designer" :layout="layout" :chachelis="widgets"/>-->
        <!--        <layout v-if="!editMode" :layout="layout" :chachelis="widgets" :data="data"/>-->
        <div class="grid-stack" ref="grid">

            <div :ref="widget.id" v-for="widget in activeWidgets" :key="widget.id"
                 @dblclick="editOption(widget)">

                <component class="grid-stack-item-content" :is="widget.component" :meta="widget"/>

            </div>

        </div>

        <portal to="modals">

            <create-widget-modal v-if="!closeModal"
                                 :widgets="widgets"
                                 :edit-widget="selectedWidget"
                                 @close="resetModal"
                                 @create="addWidget"
                                 @update="updateWidget"/>

        </portal>

    </div>

</template>

<script>

    import Designer from '@shellybits/v-chacheli/dist/ChacheliDesigner'
    import Layout from '@shellybits/v-chacheli/dist/ChacheliLayout'
    import '@shellybits/v-chacheli/dist/ChacheliDesigner.css'
    import '@shellybits/v-chacheli/dist/ChacheliLayout.css'
    import resource from '~~nova~~/store/resources'
    import 'gridstack/dist/gridstack.all'
    import 'gridstack/dist/gridstack.min.css'
    import 'gridstack/dist/gridstack-extra.css'
    import CreateWidgetModal from './CreateWidgetModal'

    export default {
        name: 'app',
        components: { CreateWidgetModal, Designer, Layout },
        data() {

            const resourceName = this.$route.params.resource
            const { columns, rows, widgets, presets, filters } = Nova.config[ 'nova-bi' ]

            this.$store.registerModule(resourceName, resource)
            this.$store.commit(`${ resourceName }/storeFilters`, filters)

            return {
                filters,
                resourceName,
                widgets,
                presets,
                selectedWidget: null,
                activeWidgets: [],
                closeModal: true,
                gridstack: null,
                idCounter: 10,
                layout: {
                    cols: columns,
                    rows: rows
                },
                editMode: false,
                data: {}
            }

        },
        mounted() {

            const grid = this.gridstack = GridStack.init({ cellHeight: '100px', float: true }, this.$refs.grid)

            grid.on('change', function (event, items) {
                console.log(items)
            })

            for (const preset of this.presets) {

                const widget = this.findWidgetByKey(preset.widget.key)

                this.addWidget(widget, preset.options, preset.coordinates)

            }

        },
        methods: {
            findWidgetByKey(key) {
                return this.widgets.find(widget => widget.key === key)
            },
            editOption(widget) {

                this.selectedWidget = widget
                this.closeModal = false

            },
            filterChanged() {

                Nova.$emit('NovaFilterUpdate', this.$store.getters[ `${ this.resourceName }/currentEncodedFilters` ])

            },
            updateWidget(widget, options) {

                const activeWidget = this.activeWidgets.find(({ id }) => id === widget.id)

                activeWidget.options = options

                Nova.$emit(`widget-${ activeWidget.id }-update`)

                this.resetModal()

            },
            resetModal() {

                this.closeModal = true
                this.selectedWidget = null

            },
            addWidget(widget, options, coordinates = null) {

                const id = Date.now()

                this.activeWidgets.push({ ...widget, id, options: options })

                this.$nextTick(() => {

                    const grid = this.gridstack
                    const widget = this.$refs[ id ]

                    grid.makeWidget(widget)

                    grid.minWidth(widget, 2)
                    grid.minHeight(widget, 2)

                    if (coordinates) {

                        grid.move(widget, coordinates.x, coordinates.y)
                        grid.resize(widget, coordinates.width, coordinates.height)

                    }

                    grid.compact()

                    this.resetModal()

                })

            }
        }
    }

</script>

<style lang="scss">

    [data-testid="content"] {
        height: calc(100% - 3.75rem);
    }

    .chacheli-layout .chacheli > * {
        height: 100%;
        display: flex;
        overflow: hidden;
    }

    .nova-bi {
        width: 100%;
        height: 100%;
        display: flex;
        flex-direction: column;
    }

    .chacheli .content {
        min-width: initial;
        width: initial;
        max-width: initial;
    }
</style>


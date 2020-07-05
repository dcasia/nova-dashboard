<template>

    <div class="nova-bi">

        <card class="flex p-4 justify-between" :class="{ 'rounded-b-none': openFilterView }">

            <div class="flex flex-col justify-center">
                <h1 class="flex text-90 font-normal text-2xl">{{ title }}</h1>
                <p class="mt-1 text-90 leading-tight" v-if="subtitle">
                    {{ subtitle }}
                </p>
            </div>

            <div class="flex items-center">

                <button role="button"
                        class="rounded active:outline-none active:shadow-outline focus:outline-none focus:shadow-outline mr-2"
                        @click="closeModal = false">

                    <div
                        class="h-dropdown-trigger text-white font-bold flex items-center cursor-pointer select-none px-3 border-2 border-30 rounded bg-primary border-primary">
                        Add Widget
                    </div>

                </button>

                <dropdown v-if="filters.length > 0" @click.native="openFilterView = !openFilterView">

                    <dropdown-trigger class="bg-30 px-3 border-2 border-30 rounded"
                                      :class="{ 'bg-primary border-primary': openFilterView }"
                                      :active="openFilterView">

                        <icon type="filter" :class="openFilterView ? 'text-white' : 'text-80'"/>

                        <!--                        <span v-if="openFilterView" class="ml-2 font-bold text-white text-80">-->
                        <!--                            {{ activeFilterCount }}-->
                        <!--                        </span>-->

                    </dropdown-trigger>

                </dropdown>

            </div>

        </card>

        <card v-if="openFilterView" class="flex flex-wrap rounded-t-none border-t border-40">

            <component class="flex flex-col inline-flex w-1/2"
                       v-for="filter in filters"
                       :key="filter.name"
                       :resource-name="resourceName"
                       :filter-key="filter.class"
                       :is="filter.component"
                       @input="filterChanged"
                       @change="filterChanged"/>

        </card>

        <div class="grid-stack flex-1 -mx-2 mt-8" ref="grid">

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

    import resource from '~~nova~~/store/resources'
    import 'gridstack/dist/gridstack.all'
    import 'gridstack/dist/gridstack.min.css'
    import 'gridstack/dist/gridstack-extra.css'
    import CreateWidgetModal from './CreateWidgetModal'

    export default {
        name: 'app',
        components: { CreateWidgetModal },
        data() {

            const resourceName = this.$route.params.resource
            const { title, subtitle, widgets, presets, filters } = Nova.config[ 'nova-bi' ]

            this.$store.registerModule(resourceName, resource)
            this.$store.commit(`${ resourceName }/storeFilters`, filters)

            return {
                title,
                subtitle,
                filters,
                resourceName,
                widgets,
                presets,
                openFilterView: true,
                selectedWidget: null,
                activeWidgets: [],
                closeModal: true,
                gridstack: null,
                idCounter: 10,
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

    .nova-bi {
        width: 100%;
        height: 100%;
        display: block;
    }

</style>


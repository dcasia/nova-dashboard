<template>

    <loading-card :loading="loading" class="nova-bi bg-transparent shadow-none">

        <div v-if="loading" style="height: 300px"/>

        <template v-else>

            <card class="flex p-4 justify-between nova-bi__menu"
                  :class="{ 'rounded-b-none': openFilterView, 'p-8': openFilterView }">

                <div class="flex flex-col justify-center px-2">
                    <h1 class="flex text-90 font-normal text-2xl">{{ responseData.title }}</h1>
                    <p class="mt-1 text-90 leading-tight" v-if="responseData.subtitle">
                        {{ responseData.subtitle }}
                    </p>
                </div>

                <div class="flex items-center">

                    <button v-if="options.enableAddWidgetButton" role="button"
                            class="rounded active:outline-none active:shadow-outline focus:outline-none focus:shadow-outline mr-2"
                            @click="closeModal = false">

                        <div
                                class="h-dropdown-trigger text-white font-bold flex items-center cursor-pointer select-none px-3 border-2 border-30 rounded bg-primary border-primary">
                            Add Widget
                        </div>

                    </button>

                    <dropdown v-if="responseData.filters.length > 0" @click.native="openFilterView = !openFilterView">

                        <dropdown-trigger class="bg-30 px-3 border-2 border-30 rounded"
                                          :class="{ 'bg-primary border-primary': openFilterView }"
                                          :active="openFilterView">

                            <icon type="filter" :class="openFilterView ? 'text-white' : 'text-80'"/>

                            <!--                        <span v-if="openFilterView" class="ml-2 font-bold text-white text-80">-->
                            <!--                            {{ activeFilterCount }}-->
                            <!--                        </span>-->

                        </dropdown-trigger>

                    </dropdown>

                    <button role="button"
                            class="rounded active:outline-none active:shadow-outline focus:outline-none focus:shadow-outline ml-2"
                            @click="closeModal = false">

                        <div
                                class="h-dropdown-trigger text-white font-bold flex items-center cursor-pointer select-none px-3 border-2 border-30 rounded bg-primary border-primary">

                            <svg xmlns="http://www.w3.org/2000/svg"
                                 viewBox="0 0 24 24"
                                 width="24"
                                 height="24">

                                <path fill="currentColor"
                                      d="M9 4.58V4c0-1.1.9-2 2-2h2a2 2 0 0 1 2 2v.58a8 8 0 0 1 1.92 1.11l.5-.29a2 2 0 0 1 2.74.73l1 1.74a2 2 0 0 1-.73 2.73l-.5.29a8.06 8.06 0 0 1 0 2.22l.5.3a2 2 0 0 1 .73 2.72l-1 1.74a2 2 0 0 1-2.73.73l-.5-.3A8 8 0 0 1 15 19.43V20a2 2 0 0 1-2 2h-2a2 2 0 0 1-2-2v-.58a8 8 0 0 1-1.92-1.11l-.5.29a2 2 0 0 1-2.74-.73l-1-1.74a2 2 0 0 1 .73-2.73l.5-.29a8.06 8.06 0 0 1 0-2.22l-.5-.3a2 2 0 0 1-.73-2.72l1-1.74a2 2 0 0 1 2.73-.73l.5.3A8 8 0 0 1 9 4.57zM7.88 7.64l-.54.51-1.77-1.02-1 1.74 1.76 1.01-.17.73a6.02 6.02 0 0 0 0 2.78l.17.73-1.76 1.01 1 1.74 1.77-1.02.54.51a6 6 0 0 0 2.4 1.4l.72.2V20h2v-2.04l.71-.2a6 6 0 0 0 2.41-1.4l.54-.51 1.77 1.02 1-1.74-1.76-1.01.17-.73a6.02 6.02 0 0 0 0-2.78l-.17-.73 1.76-1.01-1-1.74-1.77 1.02-.54-.51a6 6 0 0 0-2.4-1.4l-.72-.2V4h-2v2.04l-.71.2a6 6 0 0 0-2.41 1.4zM12 16a4 4 0 1 1 0-8 4 4 0 0 1 0 8zm0-2a2 2 0 1 0 0-4 2 2 0 0 0 0 4z"/>

                            </svg>

                        </div>

                    </button>

                </div>

            </card>

            <CollapseTransition :duration="250">

                <card v-if="openFilterView"
                      class="nova-bi__filter-container flex flex-wrap rounded-t-none border-t border-40 bg-30">

                    <component class="nova-bi__filter flex flex-col inline-flex w-1/2"
                               v-for="filter in responseData.filters"
                               :key="filter.name"
                               :resource-name="resourceName"
                               :filter-key="filter.class"
                               :is="filter.component"
                               @input="filterChanged"
                               @change="filterChanged"/>

                </card>

            </CollapseTransition>

            <grid class="grid-stack flex-1 -mx-2 mt-8"
                  :options="options.grid"
                  :widgets="activeWidgets"
                  :enable-edit="options.enableWidgetEditing && !this.responseData.usePreset"
                  @update="saveWidget"
                  @edit="editOption"/>

            <portal to="modals">

                <create-widget-modal v-if="!closeModal"
                                     :widgets="responseData.widgets"
                                     :edit-widget="selectedWidget"
                                     @delete="deleteWidget"
                                     @close="resetModal"
                                     @create="addWidget"
                                     @update="updateWidget"/>

            </portal>

        </template>

    </loading-card>

</template>

<script>

    import resource from '~~nova~~/store/resources'
    import CreateWidgetModal from './CreateWidgetModal'
    import { Minimum } from 'laravel-nova'
    import Grid from './Grid'
    import { CollapseTransition } from 'vue2-transitions'

    export default {
        name: 'Widget',
        components: { CreateWidgetModal, Grid, CollapseTransition },
        data() {

            const resourceName = this.$route.params.resource

            return {
                loading: true,
                resourceName,
                responseData: null,
                openFilterView: true,
                selectedWidget: null,
                activeWidgets: [],
                closeModal: true
            }

        },
        async mounted() {

            const response = await Minimum(Nova.request()
                .get(`/nova-vendor/nova-widgets/${ this.$route.params.resource }?editMode=create&editing=true`))
                .catch(error => error.response)

            if (response.status === 200) {

                this.responseData = response.data

                /**
                 * Initialize Vuex
                 */
                this.$store.registerModule(this.resourceName, resource)
                this.$store.commit(`${ this.resourceName }/storeFilters`, this.responseData.filters)

                this.loading = false
                this.$nextTick(() => this.initialize())

            } else if (response.status === 404) {

                this.$router.push({ name: '404' })

            } else {

                Nova.error(response.data.message)

            }

        },
        computed: {
            options() {
                return _.merge({
                    enableAddWidgetButton: true,
                    enableWidgetEditing: true,
                    expandFilterByDefault: true,
                    grid: {
                        useCssTransforms: false,
                        breakpoint: 'none',
                        numberOfCols: 6,
                        compact: false,
                        breakpointWidth: Infinity,
                        rowHeight: 150
                    }
                }, this.responseData.options)
            }
        },
        methods: {
            initialize() {

                this.openFilterView = this.options.expandFilterByDefault

                for (const setting of this.responseData.data) {

                    const widget = this.findWidgetByKey(setting.widget.key)

                    this.addWidget(widget, setting.options, setting.id, setting.coordinates)

                }

                // for (const preset of this.responseData.presets) {
                //
                //     const widget = this.findWidgetByKey(preset.widget.key)
                //     // console.log(widget)
                //     // this.addWidget(widget, preset.options, preset.coordinates)
                //
                // }

            },
            findWidgetByKey(key) {

                return this.responseData.widgets.find(widget => widget.key === key)

            },
            editOption(widget) {

                if (this.options.enableWidgetEditing) {

                    this.selectedWidget = widget
                    this.closeModal = false

                }

            },
            filterChanged() {

                this.debouncer(() => {
                    Nova.$emit('NovaFilterUpdate', this.$store.getters[ `${ this.resourceName }/currentEncodedFilters` ])
                })

            },
            debouncer: _.debounce(callback => callback(), 50),
            async saveWidget(widget) {

                /**
                 * If the widgets has been initialized in preset mode, do not attempt to persist the data to the database
                 */
                if (this.responseData.usePreset) {

                    return

                }

                const response = await Nova.request()
                    .post(`/nova-vendor/nova-widgets/update/${ this.$route.params.resource }`, widget)
                    .catch(error => error.response)

                if (response.status === 200) {

                    if (widget.id !== response.data) {

                        const newWidgetId = response.data

                        Nova.$emit(`widget-${ widget.id }-destroyed`, newWidgetId)

                        widget.id = newWidgetId

                    }

                }

            },
            async updateWidget(widget, options) {

                const activeWidget = this.activeWidgets.find(({ id }) => id === widget.id)

                activeWidget.options = options

                await this.saveWidget(activeWidget)

                Nova.$emit(`widget-${ activeWidget.id }-update`)

                this.resetModal()

            },
            resetModal() {

                this.closeModal = true
                this.selectedWidget = null

            },
            async deleteWidget(widget) {

                const { id } = widget

                const response = await Nova.request()
                    .post(`/nova-vendor/nova-widgets/delete/${ this.$route.params.resource }`, { id })
                    .catch(error => error.response)

                if (response.status === 200 && response.data) {

                    const widgetIndex = this.activeWidgets.findIndex(({ id }) => id === widget.id)

                    this.activeWidgets.splice(widgetIndex, 1)

                    Nova.$emit(`widget-${ widget.id }-deleted`)

                    this.resetModal()

                } else {

                    // @todo
                    alert('Failed to delete widget.')

                }


            },
            async addWidget(widget, options, id, coordinates = { x: 0, y: 0, width: 2, height: 1 }) {

                id = id ? id : Date.now() * Math.random()

                const widgetData = { ...widget, id, options, coordinates }

                this.$nextTick(() => this.resetModal())

                await this.saveWidget(widgetData)

                this.activeWidgets.push(widgetData)

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

    .nova-bi__menu {

        transition: border-radius 50ms 200ms, padding 250ms;

        &.rounded-b-none {

            transition: border-radius 0ms 0ms, padding 250ms;

        }

    }

    .nova-bi__filter-container > .nova-bi__filter:last-child {

        padding-bottom: 1.5rem;

    }

    .nova-bi__filter {
        padding-top: 1rem;
        padding-left: 2rem;
        padding-right: 2rem;
    }

</style>


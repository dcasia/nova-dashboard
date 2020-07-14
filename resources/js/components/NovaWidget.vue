<template>

    <loading-card :loading="loading" class="nova-bi bg-transparent shadow-none">

        <div v-if="loading" style="height: 300px"/>

        <template v-else>

            <card class="flex p-4 justify-between" :class="{ 'rounded-b-none': openFilterView }">

                <div class="flex flex-col justify-center">
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

                </div>

            </card>

            <card v-if="openFilterView" class="flex flex-wrap rounded-t-none border-t border-40">

                <component class="flex flex-col inline-flex w-1/2"
                           v-for="filter in responseData.filters"
                           :key="filter.name"
                           :resource-name="resourceName"
                           :filter-key="filter.class"
                           :is="filter.component"
                           @input="filterChanged"
                           @change="filterChanged"/>

            </card>

            <grid class="grid-stack flex-1 -mx-2 mt-8" :widgets="activeWidgets"/>

            <!--            <div class="grid-stack flex-1 -mx-2 mt-8" ref="grid">-->

            <!--                <div :ref="widget.id" v-for="widget in activeWidgets" :key="widget.id"-->
            <!--                     @dblclick="editOption(widget)">-->

            <!--                    <component class="grid-stack-item-content" :is="widget.component" :meta="widget"/>-->

            <!--                </div>-->

            <!--            </div>-->

            <portal to="modals">

                <create-widget-modal v-if="!closeModal"
                                     :widgets="responseData.widgets"
                                     :edit-widget="selectedWidget"
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

    export default {
        name: 'Widget',
        components: { CreateWidgetModal, Grid },
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

            const response = await Minimum(Nova.request().get(`/nova-vendor/nova-widgets/${ this.$route.params.resource }`)).catch(error => error.response)

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
                    gridOptions: {
                        cellHeight: '100px',
                        float: true,
                        staticGrid: false
                    }
                }, this.responseData.options)
            }
        },
        methods: {
            initialize() {

                this.openFilterView = this.options.expandFilterByDefault

                for (const preset of this.responseData.presets) {

                    const widget = this.findWidgetByKey(preset.widget.key)

                    this.addWidget(widget, preset.options, preset.coordinates)

                }

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

                const id = Date.now() * Math.random()

                this.activeWidgets.push({ ...widget, id, options, coordinates })

                this.$nextTick(() => this.resetModal())

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


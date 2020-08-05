<template>

    <loading-card :loading="loading" class="nova-bi bg-transparent shadow-none">

        <div v-if="loading" style="height: 300px"/>

        <template v-else>

            <card class="flex p-4 justify-between nova-bi__menu"
                  :class="{ 'rounded-b-none p-8': shouldExpandFilterView }">

                <div class="flex flex-col justify-center px-2">

                    <p class="mt-1 text-60 leading-tight" v-if="responseData.title">
                        {{ responseData.title }}
                    </p>

                    <view-select @change="onViewSelected"
                                 :selected-view="selectedViewKey"
                                 :views="responseData.views"
                                 class="mr-2"/>

                </div>

                <div class="flex items-center">

                    <FadeTransition>

                        <button v-if="allowWidgetEditing"
                                role="button"
                                class="rounded active:outline-none active:shadow-outline focus:outline-none focus:shadow-outline mr-2"
                                @click="closeWidgetCreationModal = false">

                            <div class="h-dropdown-trigger text-white font-bold flex items-center cursor-pointer select-none px-3 border-2 border-30 rounded bg-primary border-primary">
                                {{ __('Add Widget') }}
                            </div>

                        </button>

                    </FadeTransition>

                    <FadeTransition>

                        <dropdown v-if="filters.length > 0" @click.native="openFilterView = !openFilterView">

                            <dropdown-trigger class="bg-30 px-3 border-2 border-30 rounded"
                                              :class="{ 'bg-primary border-primary': shouldExpandFilterView }"
                                              :active="shouldExpandFilterView">

                                <icon type="filter" :class="shouldExpandFilterView ? 'text-white' : 'text-80'"/>

                                <!--                        <span v-if="openFilterView" class="ml-2 font-bold text-white text-80">-->
                                <!--                            {{ activeFilterCount }}-->
                                <!--                        </span>-->

                            </dropdown-trigger>

                        </dropdown>

                    </FadeTransition>

                    <FadeTransition>

                        <button v-if="actions.length > 0"
                                role="button"
                                class="rounded active:outline-none active:shadow-outline focus:outline-none focus:shadow-outline ml-2"
                                @click="closeActionModal = false">

                            <div class="h-dropdown-trigger text-white font-bold flex items-center cursor-pointer select-none px-3 border-2 border-30 rounded bg-primary border-primary">

                                <icon type="play" class="text-white" style="margin-left: 7px;"/>

                            </div>

                        </button>

                    </FadeTransition>

                </div>

            </card>

            <CollapseTransition :duration="250">

                <card v-if="shouldExpandFilterView"
                      class="nova-bi__filter-container flex flex-wrap rounded-t-none border-t border-40 bg-30">

                    <component class="nova-bi__filter flex flex-col inline-flex w-1/2"
                               v-for="filter in filters"
                               :key="filter.name"
                               :resource-name="dashboardKey"
                               :filter-key="filter.class"
                               :is="filter.component"
                               @input="filterChanged"
                               @change="filterChanged"/>

                </card>

            </CollapseTransition>

            <grid class="grid-stack flex-1 -mx-2 mt-8"
                  :options="options.grid"
                  :widgets="activeWidgets"
                  :allow-edit="allowWidgetEditing"
                  @moved="updateCoordinates"
                  @edit="editOption"/>

            <portal to="modals" transition="fade-transition">

                <create-widget-modal v-if="!closeWidgetCreationModal"
                                     :schemas="schemas"
                                     :dashboard-key="dashboardKey"
                                     :view-key="activeView.uriKey"
                                     @created="appendNewWidget"
                                     @close="resetModal"
                                     @create="addWidget"/>

                <edit-widget-modal v-if="selectedWidgetForEditing"
                                   :widget="selectedWidgetForEditing"
                                   :dashboard-key="dashboardKey"
                                   :view-key="activeView.uriKey"
                                   @deleted="widgetWasDeleted"
                                   @close="closeEditWidgetModal"
                                   @updated="widgetWasUpdated"/>

                <action-modal v-if="!closeActionModal"
                              :dashboard-key="dashboardKey"
                              :view-key="activeView.uriKey"
                              :actions="actions"
                              @close="closeActionModal = true"/>

            </portal>

        </template>

    </loading-card>

</template>

<script>

import resource from '~~nova~~/store/resources'
import CreateWidgetModal from './Modals/CreateWidgetModal'
import EditWidgetModal from './Modals/EditWidgetModal'
import ActionModal from './Modals/ActionModal'
import ViewSelect from './Inputs/ViewSelect'
import { Minimum } from 'laravel-nova'
import Grid from './Grid'
import { CollapseTransition, FadeTransition } from 'vue2-transitions'

export default {
    name: 'Dashboard',
    components: {
        CreateWidgetModal,
        EditWidgetModal,
        Grid,
        CollapseTransition,
        FadeTransition,
        ActionModal,
        ViewSelect
    },
    data() {

        const dashboardKey = this.$route.params.dashboardKey

        return {
            selectedViewKey: null,
            selectedViewData: [],
            loading: true,
            dashboardKey,
            cancelToken: null,
            responseData: null,
            openFilterView: true,
            selectedWidgetForEditing: null,
            activeWidgets: [],
            closeWidgetCreationModal: true,
            closeActionModal: true,
            closeWidgetEditModal: true
        }

    },
    mounted() {

        Minimum(Nova.request({
            method: 'get',
            url: `/nova-vendor/nova-dashboard/${ this.dashboardKey }`,
            params: {
                editMode: 'create',
                editing: true
            }
        })).then(response => {

            this.responseData = response.data

            /**
             * Initialize Vuex
             */
            this.$store.registerModule(this.dashboardKey, resource)
            this.loading = false
            this.openFilterView = this.options.expandFilterByDefault

            if (!this.selectedViewKey && this.responseData.activeViewData) {

                this.selectedViewKey = this.responseData.activeViewData.uriKey
                this.selectedViewData = this.responseData.activeViewData.data

            }

        }).catch(error => {

            if (error.response.status === 404) {

                this.$router.push({ name: '404' })

            } else {

                Nova.error(error.response.data.message)

            }

        })

    },
    watch: {
        selectedViewKey() {

            this.$store.commit(`${ this.dashboardKey }/storeFilters`, this.filters)
            this.activeWidgets = []

            /**
             * Give some time for the transition to finish
             */
            setTimeout(() => this.initialize(), 250)

        }
    },
    computed: {
        allowWidgetEditing() {

            return this.activeView.meta[ 'editable' ] ?? false

        },
        shouldExpandFilterView() {

            if (this.filters.length === 0) {

                return false

            }

            return this.openFilterView

        },
        activeView() {

            return this.responseData.views.find(view => view.uriKey === this.selectedViewKey)

        },
        actions() {

            if (this.activeView) {

                return this.activeView.actions

            }

            return this.responseData.actions

        },
        filters() {

            if (this.activeView) {

                return this.activeView.filters

            }

            return this.responseData.filters

        },
        views() {
            return this.responseData.views
        },
        schemas() {

            if (this.activeView) {

                return this.activeView.schemas

            }

            return {}

        },
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
        debouncer: _.debounce(callback => callback(), 100),
        onViewSelected(viewKey) {

            if (this.cancelToken) {

                this.cancelToken.cancel('Operation canceled by the user.')

            }

            const currentToken = this.cancelToken = axios.CancelToken.source()

            Nova.request({
                method: 'get',
                url: '/nova-vendor/nova-dashboard/widget/view',
                cancelToken: currentToken.token,
                params: {
                    editMode: 'create',
                    editing: true,
                    dashboard: this.dashboardKey,
                    view: viewKey
                }
            }).catch(error => {

                Nova.error(this.__('There was a problem fetching your view data.'))

            }).then(response => {

                this.selectedViewData = response.data
                this.selectedViewKey = viewKey
                this.cancelToken = null

            })

        },
        initialize() {

            for (const state of this.selectedViewData) {

                this.appendWidget(state)

            }

        },
        findWidgetByKey(key) {

            return this.responseData.widgets.find(widget => widget.key === key)

        },
        editOption(widget) {

            if (widget.editable) {

                this.selectedWidgetForEditing = widget
                this.closeWidgetEditModal = false

            }

        },
        filterChanged() {

            this.debouncer(() => {
                Nova.$emit('NovaFilterUpdate', this.$store.getters[ `${ this.dashboardKey }/currentEncodedFilters` ])
            })

        },
        updateCoordinates(widget) {

            Nova.request({
                method: 'post',
                url: '/nova-vendor/nova-dashboard/widget/update-coordinates',
                data: {
                    id: widget.id,
                    dashboard: this.dashboardKey,
                    view: widget.viewKey,
                    widget: widget.widgetKey,
                    coordinates: widget.coordinates
                }
            }).catch(error => {

                Nova.error(this.__('There was a problem saving your latest changes.'))

            }).then(response => {

                Nova.$emit(`widget-${ widget.id }-update-coordinates`, widget)

            })

        },
        async widgetWasUpdated(updatedWidgetId) {

            this.closeEditWidgetModal()

        },
        closeEditWidgetModal() {

            this.selectedWidgetForEditing = null

        },
        resetModal() {

            this.closeWidgetCreationModal = true
            this.selectedWidgetForEditing = null

        },
        async widgetWasDeleted(deletedId) {

            this.closeEditWidgetModal()

            const widgetIndex = this.activeWidgets.findIndex(({ id }) => id === deletedId)

            this.activeWidgets.splice(widgetIndex, 1)

            Nova.$emit(`widget-${ deletedId }-deleted`)

        },
        appendNewWidget(widgetData) {

            this.activeWidgets.push(widgetData)

        },
        appendWidget({ data: { coordinates, id, options, ...meta }, uriKey, editable }) {

            this.activeWidgets.push({
                id,
                editable,
                meta,
                schema: this.schemas[ uriKey ],
                options: options,
                coordinates: coordinates || { x: 0, y: 0, width: 2, height: 1 },
                dashboardKey: this.dashboardKey,
                viewKey: this.selectedViewKey,
                widgetKey: uriKey
            })

        },
        async addWidget({ uriKey, component }, options) {

            this.$nextTick(() => this.resetModal())

            this.activeWidgets.push({
                id: Date.now() * Math.random(),
                component: component,
                coordinates: { x: 0, y: 0, width: 2, height: 1 },
                widgetKey: uriKey,
                dashboardKey: this.dashboardKey,
                viewKey: this.selectedViewKey,
                options: options
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


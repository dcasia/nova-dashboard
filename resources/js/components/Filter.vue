<template>

    <Card class="nova-dashboard-filter mb-4 transition-padding transition"
          :style="{ '--columns-desktop': columns || 2 }"
          :class="{ '--active px-1 pb-1': filtersAreApplied, 'px-1': !filtersAreApplied, '--expanded': expanded }">

        <div :class="{ 'h-11': expanded, 'h-14': !expanded }" class="w-full flex items-center transition-height">

            <Dropdown placement="bottom-start">

                <Button
                    class="shadow-none"
                    :variant="filtersAreApplied ? 'solid' : 'ghost'"
                    padding="tight"
                    :aria-label="__('Views Dropdown')">

                    <div class="mr-2">

                        <template v-if="activeView.icon?.trim()?.startsWith('<svg')">
                            <div v-html="activeView.icon"/>
                        </template>

                        <Icon v-else :name="activeView.icon"/>

                    </div>

                    <div>{{ activeView.name }}</div>

                </Button>

                <template #menu>

                    <DropdownMenu width="auto" class="px-1">

                        <ScrollWrap :height="250" class="divide-y divide-gray-100 dark:divide-gray-800 divide-solid">

                            <div v-if="views.length > 0" class="py-1">

                                <DropdownMenuItem
                                    v-for="view in views"
                                    :key="view.key"
                                    as="button"
                                    class="border-none"
                                    @click="() => onViewToggle(view)"
                                    :title="view.name">

                                    {{ view.name }}

                                </DropdownMenuItem>

                            </div>

                        </ScrollWrap>

                    </DropdownMenu>

                </template>

            </Dropdown>

            <div class="toolbar-button pr-2 md:pr-3 flex flex-1 justify-between filter__header">

                <button
                    v-if="!filtersAreApplied"
                    class="py-2 w-full text-xs uppercase tracking-wide text-center font-bold focus:outline-none relative flex justify-end items-center"
                    @click="expanded = !expanded">

                    <div>
                        {{ __('Filters') }}
                    </div>

                    <Icon name="chevron-down"
                          class="ml-1 transition-all w-4 h-4"
                          :class="{ 'rotate-180': expanded }"/>

                </Button>

                <div v-if="filtersAreApplied" class="w-full">

                    <button
                        class="py-2 ml-auto block text-xs uppercase tracking-wide font-bold focus:outline-none cursor-pointer"
                        @click="clearFilters">

                        {{ __('Reset Filters') }}

                    </button>

                </div>

            </div>

        </div>

        <Collapse :when="expanded">

            <div class="filter__inner bg-gray-900 rounded p-4">

                <div v-if="activeView && activeView.filters.length">

                    <div class="flex flex-wrap">

                        <div v-for="filter in activeView.filters" :key="filter.name" class="filter__loop">

                            <component
                                :is="filter.component"
                                :filter-key="filter.class"
                                :resource-name="resourceName"
                                @change="onChange"
                                @input="onChange"/>

                        </div>

                    </div>

                </div>

            </div>

            <Collapse :when="!filtersAreApplied">

                <div class="flex justify-center items-center cursor-pointer pb-1"
                     @click="expanded = !expanded">

                    <Icon name="chevron-up" class="h-3 translate-y-[2px]"/>

                </div>

            </Collapse>

        </Collapse>

    </Card>

</template>

<script>

    import Filterable from '@/mixins/Filterable'
    import InteractsWithQueryString from '@/mixins/InteractsWithQueryString'
    import { Collapse } from 'vue-collapsed'
    import { Button, Icon } from 'laravel-nova-ui'

    export default {
        components: { Collapse, Button, Icon },
        mixins: [ Filterable, InteractsWithQueryString ],
        emits: [ 'toggle' ],
        props: [
            'views',
            'activeView',
            'filters',
            'columns',
            'resource',
            'resourceName',
            'viaResource',
            'viaResourceId',
            'viaRelationship',
        ],
        data() {
            return {
                expanded: false,
            }
        },
        watch: {
            activeView: {
                immediate: true,
                handler(view) {
                    this.updateQueryString({ view: view.key })
                },
            },
        },
        methods: {
            clearFilters() {

                this.clearSelectedFilters()
                this.notify()

            },
            onChange() {

                this.filterChanged()
                this.notify()

            },
            notify() {

                Nova.$emit(
                    `${ this.resourceName }-updated`,
                    this.$store.getters[ `${ this.resourceName }/currentEncodedFilters` ],
                    this.resource,
                )

            },
            onViewToggle(view) {

                this.$emit('toggle', view)
                this.$nextTick(() => this.initializeState())

                if (this.expanded === false) {
                    this.expanded = !this.filtersAreApplied
                }

            },
        },
        computed: {
            filtersAreApplied() {
                return this.$store.getters[ `${ this.resourceName }/filtersAreApplied` ]
            },
            initialEncodedFilters() {
                return this.queryStringParams[ this.filterParameter ] || ''
            },
            pageParameter() {
                return 0
            },
        },
        async created() {
            await this.initializeState()
        },
        beforeMount() {
            this.expanded = this.filtersAreApplied
        },
    }

</script>

<style lang="scss" scoped>

    .dark .nova-dashboard-filter {

        &.\--expanded {
            @apply bg-gray-700;
        }

        &.\--active {

            @apply bg-primary-500;

            .filter__header {
                @apply text-gray-900;
            }

        }

        .filter__inner {
            @apply bg-gray-900;
        }

        .filter__header {
            @apply text-gray-400;
        }

        .filter__loop {
            &:hover {
                @apply border-gray-800;
            }
        }

    }

    .nova-dashboard-filter {

        &.\--expanded {
            @apply bg-gray-200;
        }

        &.\--active {

            @apply bg-primary-500;

            .filter__header {
                @apply text-white;
            }

        }

        .filter__inner {
            @apply bg-white;
        }

        .filter__header {
            @apply text-gray-600;
        }

        --columns-mobile: 1;
        --columns-desktop: 2;

        .filter__loop {

            width: calc(100% / var(--columns-mobile));
            margin: 1px;

            @apply border border-transparent rounded transition-all;

            &:hover {
                @apply border-gray-200;
            }

        }

        @screen lg {

            .filter__loop {
                width: calc(100% / var(--columns-desktop) - 2px);
            }

        }

    }

</style>

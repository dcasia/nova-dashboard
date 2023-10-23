<template>

    <Card class="nova-dashboard-filter mb-4 transition-padding"
          :style="{ '--columns-desktop': columns || 2 }"
          :class="{ '--active px-1 pb-1': filtersAreApplied, 'px-1': !filtersAreApplied, '--expanded': collapsed }">

        <div :class="{ 'h-11': collapsed, 'h-14': !collapsed }" class="w-full flex items-center flex transition-height">

            <Dropdown class="">

                <slot name="sr-only">
                    <span class="sr-only">{{ __('Views') }}</span>
                </slot>

                <DropdownTrigger
                    :show-arrow="false"
                    :class="{
                        'dark:hover:bg-gray-800': !filtersAreApplied && collapsed === false,
                        'a dark:hover:bg-gray-800': !filtersAreApplied && collapsed === true,
                        'dark:hover:bg-primary-600 text-gray-800': filtersAreApplied,
                    }"
                    class="rounded hover:bg-gray-200 focus:outline-none focus:ring">

                    <BasicButton class="flex items-center">

                        <Icon v-if="activeView.icon" :solid="true" :type="activeView.icon" class="mr-2"/>

                        <div>{{ activeView.name }}</div>

                    </BasicButton>

                </DropdownTrigger>

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
                    class="pb-1 pt-2 w-full block text-xs uppercase tracking-wide text-center font-bold focus:outline-none relative flex justify-end items-center"
                    @click="collapsed = !collapsed">

                    <div>
                        {{ __('Filters') }}
                    </div>

                    <Icon type="chevron-down" width="14" class="ml-1 transition-all"
                          :class="{ 'rotate-180': collapsed }"/>

                </button>


                <div v-if="filtersAreApplied" class="w-full">

                    <button
                        class="py-2 ml-auto block text-xs uppercase tracking-wide font-bold focus:outline-none cursor-pointer"
                        @click="clearFilters">

                        {{ __('Reset Filters') }}

                    </button>

                </div>

            </div>

        </div>

        <Collapse :when="collapsed">

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
                     @click="collapsed = !collapsed">

                    <Icon type="chevron-up" height="12" class="translate-y-[2px]"/>

                </div>

            </Collapse>

        </Collapse>

    </Card>

</template>

<script>

    import Filterable from '@/mixins/Filterable'
    import InteractsWithQueryString from '@/mixins/InteractsWithQueryString'
    import { Collapse } from 'vue-collapsed'

    export default {
        components: { Collapse },
        mixins: [ Filterable, InteractsWithQueryString ],
        emits: [ 'filter-changed', 'toggle' ],
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
                collapsed: false,
            }
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

                this.updateQueryString({ view: view.key })
                this.$emit('toggle', view)
                this.$nextTick(() => this.initializeState())

                if (this.collapsed === false) {
                    this.collapsed = !this.filtersAreApplied
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
            this.collapsed = this.filtersAreApplied
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
                @apply text-gray-800;
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
            @apply bg-gray-300;
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
            @apply text-gray-500;
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

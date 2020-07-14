<template>

    <Dashboard class="grid" id="nova-dashboard">

        <DashLayout v-bind="options">

            <DashItem v-for="widget of widgets" :key="widget.id"
                      v-bind="widget.coordinates"
                      :id="widget.id"
                      :min-width="1"
                      :resize-handle-size="0">

                <component :is="widget.component" :meta="widget" class="grid__content"/>

                <template v-slot:resizeBottomRight>

                    <svg xmlns="http://www.w3.org/2000/svg" width="6" height="6">
                        <path fill="currentColor" d="M6 6H0V4.2h4.2V0H6v6z"/>
                    </svg>

                </template>

            </DashItem>

        </DashLayout>

    </Dashboard>

</template>

<script>

    import { Dashboard, DashItem, DashLayout } from 'vue-responsive-dash'

    export default {
        name: 'Grid',
        props: [ 'widgets' ],
        components: {
            Dashboard,
            DashLayout,
            DashItem
        },
        data() {

            return {
                options: {
                    useCssTransforms: true,
                    breakpoint: 'none',
                    numberOfCols: 6,
                    compact: false,
                    breakpointWidth: Infinity,
                }
            }

        }
    }

</script>

<style lang="scss">

    .grid__content {
        height: 100%;
        width: 100%;
        background-color: #42b9833d;
        overflow: hidden;
    }

    div[id$="-resizeBottomRight"] {

        width: 15px !important;
        height: 15px !important;
        display: flex;
        justify-content: center;
        align-items: center;

        svg {
            width: 6px;
            height: 6px;
            color: #42b983;
        }

    }

</style>

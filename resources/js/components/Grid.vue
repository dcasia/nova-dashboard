<template>

    <Dashboard class="grid" id="nova-widgets">

        <DashLayout v-bind="options">

            <DashItem class="grid__item"
                      v-for="widget of widgets" :key="widget.id"
                      v-bind="widget.coordinates"
                      :id="widget.id"
                      :min-width="1"
                      :resize-handle-size="0">

                <component :is="widget.component" :meta="widget" class="grid__content"/>

                <svg v-if="widget.options" @click="$emit('edit', widget)"
                     class="grid__settings" xmlns="http://www.w3.org/2000/svg"
                     viewBox="0 0 24 24"
                     width="24"
                     height="24">

                    <path fill="currentColor"
                          d="M9 4.58V4c0-1.1.9-2 2-2h2a2 2 0 0 1 2 2v.58a8 8 0 0 1 1.92 1.11l.5-.29a2 2 0 0 1 2.74.73l1 1.74a2 2 0 0 1-.73 2.73l-.5.29a8.06 8.06 0 0 1 0 2.22l.5.3a2 2 0 0 1 .73 2.72l-1 1.74a2 2 0 0 1-2.73.73l-.5-.3A8 8 0 0 1 15 19.43V20a2 2 0 0 1-2 2h-2a2 2 0 0 1-2-2v-.58a8 8 0 0 1-1.92-1.11l-.5.29a2 2 0 0 1-2.74-.73l-1-1.74a2 2 0 0 1 .73-2.73l.5-.29a8.06 8.06 0 0 1 0-2.22l-.5-.3a2 2 0 0 1-.73-2.72l1-1.74a2 2 0 0 1 2.73-.73l.5.3A8 8 0 0 1 9 4.57zM7.88 7.64l-.54.51-1.77-1.02-1 1.74 1.76 1.01-.17.73a6.02 6.02 0 0 0 0 2.78l.17.73-1.76 1.01 1 1.74 1.77-1.02.54.51a6 6 0 0 0 2.4 1.4l.72.2V20h2v-2.04l.71-.2a6 6 0 0 0 2.41-1.4l.54-.51 1.77 1.02 1-1.74-1.76-1.01.17-.73a6.02 6.02 0 0 0 0-2.78l-.17-.73 1.76-1.01-1-1.74-1.77 1.02-.54-.51a6 6 0 0 0-2.4-1.4l-.72-.2V4h-2v2.04l-.71.2a6 6 0 0 0-2.41 1.4zM12 16a4 4 0 1 1 0-8 4 4 0 0 1 0 8zm0-2a2 2 0 1 0 0-4 2 2 0 0 0 0 4z"/>

                </svg>

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
        props: [ 'widgets', 'options' ],
        components: {
            Dashboard,
            DashLayout,
            DashItem
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

    .grid__item:hover .grid__settings {

        opacity: 0.2;

    }

    .grid__settings {

        position: absolute;
        top: .2rem;
        right: .2rem;
        color: var(--30);
        opacity: 0;
        cursor: pointer;
        transition: opacity 300ms;

        &:hover {
            opacity: 1 !important
        }

    }

    div[id$="Placeholder"] .placeholder {
        border-radius: .5rem;
        background-color: var(--60)
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

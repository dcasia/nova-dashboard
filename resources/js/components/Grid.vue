<template>

    <Dashboard class="grid" id="nova-widgets">

        <DashLayout v-bind="options">

            <DashItem class="grid__item"
                      v-for="widget of widgets" :key="widget.id"
                      v-bind.sync="widget.coordinates"
                      @moveEnd="$emit('update', widget)"
                      @resizeEnd="$emit('update', widget)"
                      :id="widget.id"
                      :min-width="1"
                      :draggable="widget.editable"
                      :resizable="widget.editable"
                      :resize-handle-size="0">

                <component :is="widget.schema.component"
                           :meta="widget"
                           :coordinates="widget.coordinates"
                           class="grid__content"/>

                <div v-if="widget.editable"
                     class="absolute pin-r pin-t m-2 z-20"
                     @click="$emit('edit', widget)">

                    <tooltip trigger="hover">

                        <icon type="more"
                              viewBox="0 0 24 24"
                              height="16"
                              width="16"
                              class="cursor-pointer text-60 -mb-1"/>

                        <tooltip-content slot="content">
                            {{ __('Widget Options') }}
                        </tooltip-content>

                    </tooltip>

                </div>

                <!--                <div class="absolute pin-r pin-b m-2 z-20" v-if="widget.options.help || widget.data.help">-->

                <!--                    <tooltip trigger="hover">-->

                <!--                        <icon type="help"-->
                <!--                              viewBox="0 0 17 17"-->
                <!--                              height="16"-->
                <!--                              width="16"-->
                <!--                              class="cursor-pointer text-60 -mb-1"/>-->

                <!--                        <tooltip-content slot="content"-->
                <!--                                         v-html="widget.options.help || widget.data.help"-->
                <!--                                         :max-width="200"/>-->

                <!--                    </tooltip>-->

                <!--                </div>-->

                <template v-if="widget.editable" v-slot:resizeBottomRight>
                    <div class="grid__resize-handler"/>
                </template>

            </DashItem>

        </DashLayout>

    </Dashboard>

</template>

<script>

    import { Dashboard, DashItem, DashLayout } from 'vue-responsive-dash'

    export default {
        name: 'Grid',
        props: [ 'widgets', 'options', 'enableEdit' ],
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
        overflow: hidden;
        border-radius: .5rem;
        user-select: text;

    }

    .grid__settings {

        color: var(--60);

    }

    .grid__resize-handler {
        border: 2px solid var(--60);
        width: 1rem;
        height: 1rem;
        border-radius: .5rem;
        display: table;
        position: absolute;
        right: -0.6rem;
        bottom: -0.6rem;
        background: var(--60);
        transition: all 200ms;

        &:hover {
            border-radius: 0;
            right: -0.4rem;
            bottom: -0.4rem;
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
        overflow: hidden;
        resize: none;
        border-radius: .5rem;

    }

</style>

<template>

    <div class="grid-stack-item" v-bind="gridStackAttributes">

        <div class="grid-stack-item-content">

            <component
                class="h-full w-full"
                :key="`${ widget.component }.${ widget.uriKey }`"
                :is="widget.component"
                :card="widget"
            />

        </div>

    </div>

</template>

<script>

    export default {
        props: [ 'widget' ],
        computed: {
            gridStackAttributes() {
                return {
                    id: this.widget.key,
                    'gs-id': this.widget.key,
                    'gs-x': this.widget.grid.x,
                    'gs-y': this.widget.grid.y,
                    'gs-w': this.widget.grid.w,
                    'gs-h': this.widget.grid.h,
                }
            },
        },
    }

</script>

<style>
    @import "gridstack/dist/gridstack.min.css";
</style>


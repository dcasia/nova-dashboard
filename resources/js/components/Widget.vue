<template>

    <div class="grid-stack-item" :id="widget.key">

        <div class="grid-stack-item-content">

            <component
                class="h-full w-full"
                :key="`${ widget.component }.${ widget.key }.${ view }`"
                :is="widget.component"
                :card="{ ...widget, uriKey: widget.key }"
            />

        </div>

    </div>

</template>

<script>

    export default {
        props: [ 'widget', 'view' ],
    }

</script>

<style>
    @import "gridstack/dist/gridstack.min.css";
</style>


<template>
    <div class="flow-block node-block" :class="{disabled: isAdmin || (integration && integration.active)}">
        <svg xmlns="http://www.w3.org/2000/svg" height="25" viewBox="0 0 200 200"
             fill="#1e9ffb"
             class="reload_button"
             @click="ClearNode(node.entity.id)"
             width="25">
            <path
                d="M182.5,40a10,10,0,0,0-10-10h-30a20.06,20.06,0,0,0-20,20V80a10,10,0,0,0,20,0V60.5a58.74,58.74,0,0,1,15,39.5,60,60,0,1,1-60-60,10,10,0,0,0,0-20,80,80,0,1,0,80,80A78.93,78.93,0,0,0,160,50h12.5A10,10,0,0,0,182.5,40Z"/>
        </svg>
        <node :node="node" :errors="errors"></node>
    </div>
</template>
<script>
export default {
    props: ['node', 'errors', 'ClearNode','integration'],
    data() {
        return {
            integrationId: this.$parent.integrationId,
            isAdmin: this.$parent.isAdmin,
        }
    },
}
</script>
<style lang="scss">
.reload_button {
    position: absolute;
    right: 10px;
    top: 10px;
    transition: .3s;
    cursor: pointer;

    &:hover {
        opacity: .8;
    }

}
</style>

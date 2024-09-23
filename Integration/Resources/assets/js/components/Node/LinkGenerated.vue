<template>
    <div class="app-node-section">
        <div class="choosing-block">
            <div class="webhook-copy-link-block">
                <svg xmlns="http://www.w3.org/2000/svg" data-name="Layer 1" viewBox="0 0 32 32" width="25" height="25"
                     fill="#1e9ffb"
                     @click="Copylink"
                >
                    <path
                        d="M21.76,3.65V4.94H12.24a4,4,0,0,0-4,4V25.06H6.12a1.65,1.65,0,0,1-1.65-1.65V3.65A1.65,1.65,0,0,1,6.12,2h14A1.65,1.65,0,0,1,21.76,3.65Z"/>
                    <path
                        d="M25.53,6.94H12.24a2,2,0,0,0-2,2V28a2,2,0,0,0,2,2H25.53a2,2,0,0,0,2-2V8.94A2,2,0,0,0,25.53,6.94ZM24,23.59H13.74a1,1,0,1,1,0-2H24a1,1,0,0,1,0,2Zm0-4.12H13.74a1,1,0,0,1,0-2H24a1,1,0,0,1,0,2Zm0-4.12H13.74a1,1,0,0,1,0-2H24a1,1,0,0,1,0,2Z"/>
                </svg>
                <input type="text" class="main_input" readonly :value="webhookLink" name="webhook_copy_link_field"
                       ref="copyinput"/>
            </div>
        </div>
        <div class="copy_tooltip" v-if="showTooltip">
            Copied!
        </div>
    </div>
</template>
<script>
export default {
    props: ['node', 'errors'],
    data() {
        return {
            webhookLink: this.node.application_data.webhook_link,
            showTooltip: false
        }
    },
    methods: {
        Copylink() {
            let copyText = this.$refs.copyinput;
            copyText.select();
            copyText.setSelectionRange(0, 99999);
            document.execCommand('copy');
this.showTooltip = true
            setTimeout(()=>{
                this.showTooltip = false
            },600)
            window.getSelection().removeAllRanges()
        }
    }
}
</script>
<style scoped lang="scss">
svg {
    position: absolute;
    left: 8px;
    top: calc(50% - 12.5px);
    cursor: pointer;
    z-index: 1;
    &:hover {
        opacity: .8;
    }

}

.main_input {
    padding-left: 35px;
}

.copy_tooltip {
    position: absolute;
    left: 8px;
    bottom: 50px;
    background: rgba(0, 0, 0, .6);
    color: #FFFFFF;
    padding: 10px;
    border-radius: 10px;

}

.app-node-section {
    position: relative;
}
</style>

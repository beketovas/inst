<template>
    <div class="application-node-section">
        <div class="choosing-block">

            <multiselect v-model="application" placeholder="Choose an app"
                         @select="applicationChanged"
                         @remove="RemoveApp"
                         label="name" track-by="name"
                         :options="availableApplications" :option-height="300"
            >
                <template slot="singleLabel" slot-scope="props">
                    <span class="select-dropdown-text with-icon">
                        <span class="icon">
                            <img :src="props.option.icon" alt=""/>
                        </span>
                        {{ props.option.name }}
                    </span>
                </template>
                <template slot="option" slot-scope="props">
                    <div class="radio-item" :class="{active: application && application.id === props.option.id}">
                        <span class="radio-custom"></span>
                        <span class="label"></span>
                        <div class="img-block-radio">
                            <div class="img-block-content">
                                <div class="img">
                                    <img :src="props.option.icon" alt=""/>
                                </div>
                                <span class="text">{{ props.option.name }}</span>
                            </div>
                        </div>
                        <svg
                            v-if="!!props.option.connected"
                            xmlns="http://www.w3.org/2000/svg"
                             fill="#71f0b3"
                             class="connected_checkmark"
                             height="15px"  style="enable-background:new 0 0 512 512;" version="1.1"
                             viewBox="0 0 512 512" width="15px" xml:space="preserve"><path d="M448,71.9c-17.3-13.4-41.5-9.3-54.1,9.1L214,344.2l-99.1-107.3c-14.6-16.6-39.1-17.4-54.7-1.8  c-15.6,15.5-16.4,41.6-1.7,58.1c0,0,120.4,133.6,137.7,147c17.3,13.4,41.5,9.3,54.1-9.1l206.3-301.7  C469.2,110.9,465.3,85.2,448,71.9z"/></svg>
                    </div>
                </template>
                <h5 slot="noResult" class="not_found">No elements found. Consider changing the search query.</h5>
            </multiselect>
        </div>
        <div class="choosing-block" v-if="application">
            <p><a :href="instructionsPageLink" target="_blank">{{ $t('node.watch_video_tutorial') }}</a></p>
        </div>
    </div>
</template>
<script>

import Multiselect from 'vue-multiselect'

export default {
    props: ['node', 'errors'],
    components: {
        Multiselect
    },
    mounted() {
        setTimeout(() => {
            document.querySelectorAll('.multiselect__content-wrapper').forEach(item => {
                Scrollbar.init(item, {
                    alwaysShowTracks: true
                })
            })
        }, 500)
    },
    data() {
        return {
            integrationId: this.$parent.integrationId,
            integration: this.$parent.integration,
            userId: this.node.user_id,
            nodeId: this.node.entity.id,
            applicationId: this.node.application_id,
            application: this.node.application,
            availableApplications: this.node.available_applications,
            instructionsPageLink: 'https://apiway.crunch.help/',
        }
    },
    watch: {
        node() {
            this.userId = this.node.user_id;
            this.nodeId = this.node.entity.id;
            this.applicationId = this.node.application_id;
            this.application = this.node.application;
            this.availableApplications = this.node.available_applications;
        }
    },
    methods: {
        RemoveApp(){
            window.location.reload();
        },
        customLabel ({ icon, name }) {
            return `${icon} – ${name}`
        },
        applicationChanged(option) {
            let applicationId = option.id

            axios.put('/api/integrations/' + this.integrationId + '/nodes/' + this.nodeId + '/save-application', {
                application_id: applicationId,
                user_id: this.userId,
            })
                .then((response) => {
                    if (typeof response.data.redirect !== 'undefined') {
                        window.location.href = response.data.redirect;
                    } else {
                        //this.$eventBus.$emit('reloadNode');
                        window.location.reload();
                    }
                })
        },
    }
}
</script>
<style lang="scss">
.multiselect__tags {
    background: #ffffff;
    border: 2px solid #dae6f8;
    -webkit-box-sizing: border-box;
    box-sizing: border-box;
    border-radius: 10px;
    font-size: 20px;
    line-height: 130%;
    display: -webkit-box;
    display: -ms-flexbox;
    display: flex;
    -webkit-box-align: center;
    -ms-flex-align: center;
    align-items: center;
    padding: 13px 20px;
    color: #293856;
    width: 100%;
}

.multiselect__input {
    border: none;
    box-sizing: border-box;
    background: #ffffff;
    font-size: 20px;
    line-height: 130%;
    padding: 0;
}

.multiselect__placeholder {
    font-size: 20px;
    line-height: 130%;
    color: #c6ccd6;
}

.multiselect {
    position: relative;
}

.multiselect__content-wrapper {
    position: absolute;
    /* opacity: 0;
     left: -9990px;*/
    z-index: 3;
    width: 100%;
    top: calc(100% - 2px);
    background: #fff;
    border: 2px solid #dae6f8;
    border-radius: 0 0 10px 10px;
}

.multiselect__content {
    --indent: 36px;
    padding: var(--indent);
    grid-gap: var(--indent);
    display: grid !important;
    grid-template-columns: repeat(auto-fill, 122px);


}

.multiselect ul li:not(.multiselect__element) {
    grid-column: 1/4;
    text-align: center;
    font-weight: bold;
}

.multiselect--active {
    .multiselect__tags {
        border-radius: 10px 10px 0 0;
    }
}

.radio-item.active .radio-custom:before {
    content: "";
    display: block;
    position: absolute;
    top: 2px;
    right: 2px;
    bottom: 2px;
    left: 2px;
    background: #1e9ffb;
    border-radius: 100%;
}


.multiselect__single {
    word-break: break-all;

    .icon {
        height: 23px;
        width: 23px;
        display: inline-block;
        margin-right: 5px;

        img {
            max-width: 100%;
            max-height: 100%;
        }
    }
}
.connected_checkmark{
    position: absolute;
    left: 5px;
    top: 5px;
}
</style>

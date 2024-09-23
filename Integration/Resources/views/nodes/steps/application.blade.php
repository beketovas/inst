<div class="select-container">
    <div class="select-dropdown select-applications">
        <span class="select-dropdown-text with-icon" v-if="node.application">
            <span class="icon">
                <img :src="'/uploads/' + node.application.icon" alt="" />
            </span>
            @{{ node.application.name }}
        </span>
        <span class="select-dropdown-text" v-else>
            @lang('node.choose_service')
        </span>
    </div>
    <div class="dropdown applications">
        <label class="radio-item" v-for="availableApplication in node.availableApplications" :class="{active: node.appplication && availableApplication.id == node.application.id}">
            <input class="radio" name="application_id" type="radio" :value="availableApplication.id" v-model="node.application_id" v-on:change="applicationChanged"/>
            <span class="radio-custom"></span>
            <span class="label"></span>
            <div class="img-block-radio">
                <div class="img-block-content">
                    <div class="img">
                        <img :src="'/uploads/' + availableApplication.icon" alt="" />
                    </div>
                    <span class="text">@{{ availableApplication.name }}</span>
                </div>
            </div>
        </label>
    </div>
</div>
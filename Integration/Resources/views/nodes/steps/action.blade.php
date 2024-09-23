<div class="select-container">
    <div class="select-dropdown select-actions">
        <span class="select-dropdown-text" v-if="node.action">
            @{{ node.action.name }}
        </span>
        <span class="select-dropdown-text" v-else>
            @lang('node.choose_action')
        </span>
    </div>
    <ul class="dropdown list" tabindex="1">
        <li v-for="nodeAction in node.availableActions" :class="{active: node.action_id == nodeAction.id}">
            <a :data-id="nodeAction.id" @click.prevent="actionChanged" href="#">@{{ nodeAction.name }}</a>
        </li>
    </ul>
</div>
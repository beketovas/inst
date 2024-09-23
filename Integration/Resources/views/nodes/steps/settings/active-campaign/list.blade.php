<div class="select-container select">
    <div class="select-dropdown select-list" @click="selectList">
        <span class="select-dropdown-text with-icon" v-if="node.settings.list">
            @{{ node.settings.list.list_name }}
        </span>
        <span class="select-dropdown-text" v-else>
            @lang('applications.ac_choose_list')
        </span>
    </div>
    <ul class="dropdown list">
        <li v-for="list in node.availableSettings.lists" :class="{active: node.settings.list && node.settings.list.list_id == list.id}">
            <a :data-id="list.id" :data-name="list.name" @click.prevent="listChanged" href="#">@{{ list.name }}</a>
        </li>
    </ul>
</div>


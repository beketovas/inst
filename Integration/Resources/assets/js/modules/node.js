export class Node {
    constructor(node) {
        this.node = node;
        this.integration = this.node.integration;
        this.appNode = this.node.application_data.app_node;
        this.fields = this.node.application_data.fields;
        this.settings = this.node.application_data.settings;
    }

    clearField(field, entity) {
        //console.log('------------------------')
        //console.log(field);
        let nextField = this.findNextFieldById(field.id);
        //console.log('nextField');
        //console.log(nextField);
        if(this.isEmpty(nextField)) {
            //console.log('There is no next field')
            return false;
        }

        // If next field has no value
        if(!nextField.has_value) {
            //console.log('There is no value')
            return false;
        }

        // If next field has no uses fields
        if(!nextField.uses_fields) {
            //console.log('There is no uses fields')
            return false;
        }

        axios.post('/api/integrations/' + this.integration.code + '/nodes/' + this.node.entity.id + '/fields/clear-value', {
            field_id: nextField.id,
            entity: entity
        })
            .then((response) => {

            })
            .catch((error) => {

            })

        if(!this.isEmpty(nextField)) {
            this.clearField(nextField, entity);
        }

        return true;
    }

    clearSetting(field, entity) {
        //console.log('------------------------')
        //console.log(field);
        let nextField = this.findNextSettingById(field.id);
        //console.log('nextField');
        //console.log(nextField);
        if(this.isEmpty(nextField)) {
            //console.log('There is no next field')
            return false;
        }

        // If next field has no value
        if(!nextField.has_value) {
            //console.log('There is no value')
            return false;
        }

        // If next field has no uses fields
        if(!nextField.uses_fields) {
            //console.log('There is no uses fields')
            return false;
        }

        axios.post('/api/integrations/' + this.integration.code + '/nodes/' + this.node.entity.id + '/fields/clear-value', {
            field_id: nextField.id,
            entity: entity
        })
        .then((response) => {

        })
        .catch((error) => {

        })

        if(!this.isEmpty(nextField)) {
            this.clearSetting(nextField, entity);
        }

        return true;
    }

    clearDependent(field, entity) {
        let res;
        if(entity === 'Field') {
            res = this.clearSetting(field, entity);
        } else {
            res = this.clearField(field, entity);
        }
        return res;

    }

    findNextFieldById(id) {
        let res = {};
        let currIndex = null;
        this.fields.forEach(function(field, index) {
            //console.log('index ' + index);
            // Index of current field
            if(field.id === id) {
                currIndex = index;
                //console.log('currIndex ' + currIndex);
            }
            // Next field
            if(currIndex !== null && (currIndex + 1) === index) {
                //console.log('currIndex + 1 is equal to ' + index);
                res = field;
            }
        })

        return res;
    }

    findNextSettingById(id) {
        let res = {};
        let currIndex = null;
        this.settings.forEach(function(field, index) {
            // Index of current field
            if(field.id === id) {
                currIndex = index;
            }
            // Next field
            // Next field
            if(currIndex !== null && (currIndex + 1) === index) {
                //console.log('currIndex + 1 is equal to ' + index);
                res = field;
            }
        })
        return res;
    }

    isEmpty(obj) {
        for(let key in obj) {
            if(obj.hasOwnProperty(key))
                return false;
        }
        return true;
    }
}

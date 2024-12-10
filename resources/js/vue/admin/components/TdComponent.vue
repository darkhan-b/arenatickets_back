<template>
    <span v-if="field.attributes && field.attributes.length > 0">
        <template v-for="(value, vind) in field.attributes" v-if="object[value.id]">
            <template v-for="child_object in (Array.isArray(object[value.id]) ? object[value.id] : [object[value.id]])">
                <span class="d-block" v-for="(child_field,cfind) in value.value">
                    <template v-if="child_field instanceof Object">
                        <span class="d-block" v-for="(a,b) in child_field">
                            [{{ field.idattribute ? child_object[field.idattribute] : child_object["id"] }}]
                            {{ child_object[b][a].ru ? child_object[b][a].ru : child_object[b][a] }}
                        </span>
                    </template>
                    <template v-else>
                        <template v-if="child_object[child_field] && value.language">
                            <span v-if="cfind == 0">[{{ field.idattribute ? child_object[field.idattribute] : child_object["id"] }}]</span>
                            {{ child_object[child_field][value.language] }}
                        </template>
                        <template v-else-if="child_object[child_field]">
                            <span v-if="cfind == 0">[{{ field.idattribute ? child_object[field.idattribute] : child_object["id"] }}]</span>
                            {{ child_object[child_field] }}
                        </template>
                    </template>
                </span>
            </template>
        </template>
    </span>
    <span v-else>
        <span v-if="field.table_show" v-html="field.table_show[object[field_id]]"></span>
        <template v-else>
            <span v-if="field.format_number">{{ object[field_id] | formatNumber }}</span>
            <span v-else-if="field.format_date">{{ object[field_id] | formatDate('YYYY-MM-DD HH:mm') }}</span>
            <span v-else-if="field.table_image"><img v-if="object[field_id]" class="admin-table-img" :src="object[field_id]" onerror="this.src='/images/nophoto.jpeg';" alt="table image"/></span>
            <span v-else-if="field.table_html" v-html="object[field_id]"></span>
            <span v-else-if="field.table_color" :style="'background: '+object[field_id]" class="sample-color-box"></span>
            <span v-else-if="field.languages" v-html="object[field_id]['ru']"></span>
            <span v-else v-html="field.table_field ? object[field.table_field] : object[field_id]"></span>
        </template>
    </span>
</template>

<script>
    export default {
        props: {
            field: { required: true },
            field_id: { required: true },
            object: { required: true }
        },
        data: function() {
            return {
            }
        }
    }
</script>

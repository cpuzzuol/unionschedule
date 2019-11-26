<template>
    <div class="system-user-vacation-request-log-modal-container">
        <v-dialog v-model="dialog" persistent scrollable max-width="590px">
            <template v-slot:activator="{ on }">
                <v-btn color="info" text v-on="on" title="View request log"><v-icon>mdi-format-list-bulleted</v-icon></v-btn>
            </template>
            <v-card>
                <v-toolbar dark>
                    <v-toolbar-title>{{ vacationRequest.requester.first_name + ' ' + vacationRequest.requester.last_name }}'s Request Log: {{ vacationRequest.date_requested | slashdate }}</v-toolbar-title>
                    <v-spacer></v-spacer>
                    <v-btn icon dark @click="closeDialog"><v-icon>mdi-close</v-icon></v-btn>
                </v-toolbar>
                <v-card-text class="mt-4">
                    <template v-if="vacationRequest.logs.length > 0">
                        <p v-for="(log, index) in vacationRequest.logs" :key="'log-' + index">
                            <strong>[{{ log.created_at | slashdatetime }}] by {{ log.action_by.first_name + ' ' + log.action_by.last_name }} -</strong> {{ log.description }}
                        </p>
                    </template>
                    <p v-else>No logs for this vacation request.</p>
                </v-card-text>
            </v-card>
        </v-dialog>
    </div>
</template>
<script>
	import Vue from 'vue'
    export default {
        components: { },
		props: {
			vacationRequest: {
                type: Object,
                required: true
            }
		},
        created() {
        },
		data: () => ({
            dialog: false
		}),
        computed: {
        },
        methods: {
			// Do a little cleanup when dialog closes
			closeDialog() {
                this.dialog = false
            }
        },
        watch: {
        }
	}
</script>

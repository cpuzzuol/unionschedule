<template>
    <div class="admin-manage-vacation-request-modal-container">
        <v-dialog v-model="dialog" persistent scrollable max-width="590px">
            <template v-slot:activator="{ on }">
                <v-btn :color="iconProps.color" small dark v-on="on" icon class="mr-2" :title="iconProps.title"><v-icon>{{ iconProps.icon }}</v-icon></v-btn>
            </template>
            <v-card>
                <v-toolbar dark>
                    <v-toolbar-title>{{ iconProps.title }}</v-toolbar-title>
                    <v-spacer></v-spacer>
                    <v-btn icon dark @click="closeDialog"><v-icon>mdi-close</v-icon></v-btn>
                </v-toolbar>
                <v-card-text style="height: 500px;" class="mt-4">
                    <p>Please confirm you wish to set this vacation request to <strong>{{ action }}</strong> for <strong>{{ vacationRequest.requester.first_name + ' ' + vacationRequest.requester.last_name }}</strong> on <strong>{{ vacationRequest.date_requested | slashdate }}</strong>.</p>
                    <p>By default, the requester is notified when you make the decision. To disable email notification, flip the switch below. If you would like to add an explanation for this action, please add it in the box below. The requester will see this note in the email.</p>
                    <v-textarea v-model="note" label="Optional Note to Requester"></v-textarea>
                    <v-switch v-model="sendEmail" label="Email Decision to Requester"></v-switch>
                    <v-alert
                        :color="submitResult.color"
                        :value="submitResult.complete"
                        dark
                    >
                        {{ submitResult.msg }}
                    </v-alert>
                </v-card-text>
                <v-divider></v-divider>
                <v-card-actions>
                    <v-btn :color="buttonProps.color" :disabled="submitting" :loading="submitting" @click="updateRequest">{{ buttonProps.text }}</v-btn>
                    <v-btn color="secondary" :disabled="submitting" outlined @click="closeDialog">Cancel</v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>
    </div>
</template>
<script>
	import Vue from 'vue'

	export default {
		props: {
			action: {
				type: String,
                required: true
            },
			user: {
                type: Object,
                required: true
            },
            vacationRequest: {
				type: Object,
                required: true
            }
		},
        created() {
        },
		data: () => ({
            dialog: false,
            note: '',
            sendEmail: true,
            submitResult: {
            	color: 'info',
                complete: false,
                msg: ''
            },
            submitting: false
		}),
        computed: {
            buttonProps() {
                switch(this.action) {
                    case 'approve':
                        return {text: 'Approve', color: 'success darken-1'}
                    case 'deny':
                        return {text: 'Deny', color: 'error darken-1'}
                    case 'pending':
                        return {text: 'Set Pending', color: 'warning darken-1'}
                    default:
                        return
                }
            },
			iconProps() {
            	switch(this.action) {
                  case 'approve':
                  	return {icon: 'mdi-thumb-up-outline', color: 'info darken-1', title: 'Approve Vacation Request'}
                  case 'deny':
                    return {icon: 'mdi-thumb-down-outline', color: 'info darken-1', title: 'Deny Vacation Request'}
                  case 'pending':
                  	return {icon: 'mdi-account-clock-outline', color: 'info darken-1', title: 'Set Vacation Request to Pending'}
                  default:
                    return
                }
            }
        },
        methods: {
			// Do a little cleanup when dialog closes
			closeDialog() {
                this.dialog = false
                this.note = ''
                this.submitResult.complete = false
            },
            updateRequest() {
                this.submitting = true
                Vue.prototype.$http.put(`/api/vacationrequests/${this.vacationRequest.id}`,
                  {
                  	status: this.action,
                    note: this.note,
                    sendEmail: this.sendEmail
                  },
                  {
                  	headers: {
                  		'Accept': 'application/json',
                        'Authorization': 'Bearer ' + this.user.api_token
                    }
                  }
                )
                .then(response => {
                	this.submitResult.color = 'success'
                    this.submitResult.msg = 'Update successful.'

                    // Close the dialog and update the parent view by $emit-ing after 2 seconds
                    setTimeout(() => {
                        this.closeDialog()
                        this.$emit('request-updated')
                    }, 2000)
                })
                .catch(e => {
                	this.submitResult.color = 'error'
                    this.submitResult.msg = 'There was a problem submitting the request.'
                })
                .finally(response => {
                	this.submitResult.complete = true
                	this.submitting = false
                })
            }
        },
        watch: {
			dialog() {
            }
        }
	}
</script>

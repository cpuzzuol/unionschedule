<template>
    <div class="user-cancel-vacation-request-modal-container">
        <v-dialog v-model="dialog" persistent scrollable max-width="590px">
            <template v-slot:activator="{ on }">
                <v-btn color="error" small dark v-on="on" icon class="mr-2" title="Cancel this PTO Request"><v-icon>mdi-cancel</v-icon></v-btn>
            </template>
            <v-card>
                <v-toolbar dark>
                    <v-toolbar-title>Cancel PTO Request</v-toolbar-title>
                    <v-spacer></v-spacer>
                    <v-btn icon dark @click="closeDialog"><v-icon>mdi-close</v-icon></v-btn>
                </v-toolbar>
                <v-card-text class="mt-4">
                    <p>Please confirm you wish to cancel this PTO request for <strong>{{ vacationRequest.date_requested | slashdatedow }}</strong>.</p>
                    <v-alert
                        :color="submitResult.color"
                        :value="submitResult.complete"
                        dark
                    >
                        {{ submitResult.msg }}
                    </v-alert>
                </v-card-text>
                <v-divider></v-divider>
                <v-card-actions v-if="submitResult.color != 'success'">
                    <v-btn color="error" :disabled="submitting" :loading="submitting" @click="cancelRequest">Confirm Cancellation</v-btn>
                    <v-btn color="secondary" :disabled="submitting" outlined @click="closeDialog">Do Not Cancel</v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>
    </div>
</template>
<script>
	import Vue from 'vue'

	export default {
		props: {
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
            submitResult: {
            	color: 'info',
                complete: false,
                msg: ''
            },
            submitting: false
		}),
        computed: {
        },
        methods: {
            cancelRequest() {
                this.submitting = true
                Vue.prototype.$http.delete(`/api/vacationrequests/${this.vacationRequest.id}`,
                  {
                  	headers: {
                  		'Accept': 'application/json',
                        'Authorization': 'Bearer ' + this.user.api_token
                    }
                  }
                )
                .then(response => {
                	this.submitResult.color = 'success'
                    this.submitResult.msg = 'Cancellation successful.'

                    // Close the dialog and update the parent view by $emit-ing after 2 seconds
                    setTimeout(() => {
                        this.closeDialog()
                        this.$emit('request-canceled')
                    }, 2000)
                })
                .catch(e => {
                	this.submitResult.color = 'error'
                    this.submitResult.msg = 'There was a problem canceling the request. Please notify your supervisor.'
                })
                .finally(response => {
                	this.submitResult.complete = true
                	this.submitting = false
                })
            },
            // Do a little cleanup when dialog closes
            closeDialog() {
                this.dialog = false
                this.submitResult = {
                    color: 'info',
                    complete: false,
                    msg: ''
                }
            },
        },
        watch: {
			dialog() {
            }
        }
	}
</script>

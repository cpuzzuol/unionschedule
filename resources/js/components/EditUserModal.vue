<template>
    <div class="edit-user-container">
        <v-dialog v-model="dialog" fullscreen hide-overlay transition="dialog-bottom-transition">
            <template v-slot:activator="{ on }">
                <span class="primary--text clickable" v-on="on" text><slot></slot></span>
            </template>
            <v-card>
                <v-toolbar dark>
                    <v-toolbar-title>User Overview: {{ user.first_name + ' ' + user.last_name }}</v-toolbar-title>
                    <v-spacer></v-spacer>
                    <v-btn icon dark @click="closeDialog"><v-icon>mdi-close</v-icon></v-btn>
                </v-toolbar>
                <v-card-text>
                    <v-tabs
                        v-model="tab"
                        fixed-tabs
                        background-color="primary"
                        dark
                        class="mt-3"
                    >
                        <v-tabs-slider color="yellow"></v-tabs-slider>
                        <v-tab>
                            Basic Information
                        </v-tab>
                        <v-tab>
                            Vacation Request History
                        </v-tab>
                        <v-tab>
                            Action Logs
                        </v-tab>
                    </v-tabs>
                    <v-tabs-items v-model="tab">
                        <v-tab-item
                            v-for="section in tabTitles"
                            :key="section"
                        >
                            <template v-if="section == 'Basic Information'">
                                <v-card>
                                    <v-card-text>
                                        <v-form>
                                            <v-row>
                                                <v-col cols="12" xs="12" sm="6">
                                                    <v-text-field
                                                        v-model="userEditable.first_name"
                                                        label="First Name*"
                                                        filled
                                                        :error-messages="errorsFirstName"
                                                        @input="$v.userEditable.first_name.$touch()"
                                                    >
                                                    </v-text-field>
                                                </v-col>
                                                <v-col cols="12" xs="12" sm="6">
                                                    <v-text-field
                                                        v-model="userEditable.last_name"
                                                        label="Last Name*"
                                                        filled
                                                        :error-messages="errorsLastName"
                                                        @input="$v.userEditable.last_name.$touch()"
                                                    >
                                                    </v-text-field>
                                                </v-col>
                                                <v-col cols="12">
                                                    <v-text-field
                                                        v-model="userEditable.email"
                                                        label="Email*"
                                                        filled
                                                        :error-messages="errorsEmail"
                                                        @input="$v.userEditable.email.$touch()"
                                                    >
                                                    </v-text-field>
                                                </v-col>
                                                <v-col cols="12" xs="12" sm="6">
                                                    <v-text-field
                                                        v-model="userEditable.vacation_days"
                                                        label="Vacation Days"
                                                        filled
                                                        :error-messages="errorsVacationDays"
                                                        @input="$v.userEditable.vacation_days.$touch()"
                                                    >
                                                    </v-text-field>
                                                </v-col>
                                                <v-col cols="12" xs="12" md="6">
                                                    <v-switch
                                                        v-model="userEditable.is_admin"
                                                        label="Administrator"
                                                        :disabled="!canEditAdminSetting"
                                                    >
                                                        <template v-slot:label>
                                                            Administrator <span v-if="!canEditAdminSetting"><v-icon>mdi-lock</v-icon></span>
                                                        </template>
                                                    </v-switch>
                                                </v-col>
                                                <v-col cols="12" xs="12" md="6">
                                                    <v-switch
                                                        v-model="sendResetPasswordLink"
                                                    >
                                                        <template v-slot:label>
                                                            Send Reset Password Email
                                                            <v-tooltip top>
                                                                <template v-slot:activator="{ on }">
                                                                    <v-btn icon v-on="on">
                                                                        <v-icon color="grey lighten-1">mdi-information</v-icon>
                                                                    </v-btn>
                                                                </template>
                                                                <span>When you click 'Update User', this person will be sent an email<br>with a link where they can change their password.</span>
                                                            </v-tooltip>
                                                        </template>
                                                    </v-switch>
                                                </v-col>
                                            </v-row>
                                        </v-form>
                                        <v-alert
                                            :color="submitResult.color"
                                            :value="submitResult.complete"
                                            dark
                                        >
                                            {{ submitResult.msg }}
                                        </v-alert>
                                        <p class="error--text">All fields marked with * are required.</p>
                                    </v-card-text>
                                    <v-divider></v-divider>
                                    <v-card-actions>
                                        <v-btn color="success darken-1" :disabled="$v.userEditable.$invalid || submitting" :loading="submitting" @click="updateUser">Update User</v-btn>
                                        <v-btn color="secondary" :disabled="submitting" outlined @click="setUser">Reset</v-btn>
                                        <v-spacer></v-spacer>
                                        <v-btn color="error" :disabled="submitting" @click="deleteUser">Delete User</v-btn>
                                    </v-card-actions>
                                </v-card>
                            </template>
                            <template v-if="section == 'Vacation Request History'">
                                <v-card>
                                    <v-card-text>
                                        <template v-if="pendingRequestsError">
                                            <v-alert :value="true" type="error">
                                                There was a problem loading vacation requests for this user.
                                            </v-alert>
                                        </template>
                                        <template v-else>
                                            <v-row>
                                                <v-col
                                                    xs="12"
                                                    sm="12"
                                                    md="6"
                                                    lg="4"
                                                >
                                                    <v-card outlined>
                                                        <v-card-title class="title">Future Pending Requests</v-card-title>
                                                        <v-card-text>
                                                            <system-user-outstanding-requests :user="user" :pending-requests="futurePendingRequests" @request-updated="getRequestHistory"></system-user-outstanding-requests>
                                                        </v-card-text>
                                                    </v-card>
                                                </v-col>
                                                <v-spacer></v-spacer>
                                                <v-col
                                                    xs="12"
                                                    sm="12"
                                                    md="6"
                                                    lg="4"
                                                >
                                                    <v-card outlined>
                                                        <v-card-title class="title">Past Requests</v-card-title>
                                                        <v-card-text>
                                                            <v-tabs
                                                                v-model="pastRequestsTab"
                                                                fixed-tabs
                                                                background-color="primary"
                                                                dark
                                                                class="mt-3"
                                                                show-arrows
                                                            >
                                                                <v-tabs-slider color="yellow"></v-tabs-slider>
                                                                <v-tab v-for="year in yearsSince2019" :key="'past-req-tab-' + i">{{ year }}</v-tab>
                                                                <v-tabs-items v-model="pastRequestsTab">
                                                                    <v-tab-item
                                                                        v-for="year in yearsSince2019"
                                                                        :key="'past-req-tab-content-' + year"
                                                                    >
                                                                        {{ requestsForYear(year) }}
                                                                        <v-list dense two-lines>
                                                                            <v-list-item v-for="(req, index) in requestsForYear(year)" :key="'req-year-' + year + '-' + index">
                                                                                <v-list-item-title>{{ req.date_requested | slashdatedow }}</v-list-item-title>
                                                                                <v-list-item-subtitle><span :class="pastDecisionColor(req.decision)">{{ pastDecisionText(req.decision) }}</span></v-list-item-subtitle>
                                                                                <v-list-item-icon>
                                                                                    <system-user-vacation-request-log-modal :vacation-request="req"></system-user-vacation-request-log-modal>
                                                                                </v-list-item-icon>
                                                                            </v-list-item>
                                                                        </v-list>
                                                                    </v-tab-item>
                                                                </v-tabs-items>
                                                            </v-tabs>
                                                        </v-card-text>
                                                    </v-card>
                                                </v-col>
                                            </v-row>
                                        </template>
                                    </v-card-text>
                                </v-card>
                            </template>
                        </v-tab-item>
                    </v-tabs-items>
                </v-card-text>
            </v-card>
        </v-dialog>
    </div>
</template>
<script>
	import Vue from 'vue'
	import Vuelidate from 'vuelidate'
	import { required, helpers, sameAs, numeric } from 'vuelidate/lib/validators'
    import SystemUserOutstandingRequests from "./SystemUserOutstandingRequests";
		import SystemUserVacationRequestLogModal from "./SystemUserVacationRequestLogModal";
	Vue.use(Vuelidate)

	const unionSortersEmail = helpers.regex('alpha', /.+@unionsorters\.com$/);
	export default {
			components: { SystemUserVacationRequestLogModal, SystemUserOutstandingRequests },
			props: {
            apiToken: {
            	type: String,
                required: true,
            },
            canEditAdminSetting: {
                type: Boolean,
                required: true,
            },
			user: {
                type: Object,
                required: true
            }
		},
        created() {
            this.setUser()
        },
        validations () {
            return {
                userEditable: {
                    email: {
                        required,
                        //unionSortersEmail
                    },
                    first_name: {
                        required
                    },
                    last_name: {
                    	required
                    },
                    vacation_days: {
                        required,
                        numeric
                    }
                }
            }
        },
		data: () => ({
            dialog: false,
            pastRequestsTab: null,
            pendingRequests: [],
            pendingRequestsError: false,
            sendResetPasswordLink: false,
            submitResult: {
                color: 'info',
                complete: false,
                msg: ''
            },
            submitting: false,
            tab: null,
            tabTitles: ['Basic Information', 'Vacation Request History', 'Logs'],
            userEditable: []
		}),
        computed: {
            errorsEmail () {
                const errors = []
                if (!this.$v.userEditable.email.$dirty) { return errors } // clean
                !this.$v.userEditable.email.unionSortersEmail && errors.push('Must be a unionsorters.com email address')
                return errors
            },
            errorsFirstName () {
                const errors = []
                if (!this.$v.userEditable.first_name.$dirty) { return errors } // clean
                !this.$v.userEditable.first_name.required && errors.push('First name is required')
                return errors
            },
            errorsLastName () {
                const errors = []
                if (!this.$v.userEditable.last_name.$dirty) { return errors } // clean
                !this.$v.userEditable.last_name.required && errors.push('Last name is required')
                return errors
            },
            errorsVacationDays () {
                const errors = []
                !this.$v.userEditable.vacation_days.numeric && errors.push('Must be a valid number of days')
                return errors
            },
            futurePendingRequests() {
            	const today = Vue.prototype.$moment().format('YYYY-MM-DD')
            	return this.pendingRequests.filter(pr => {
            		return pr.date_requested >= today && pr.decision == 'pending'
                })
            },
            // Used for years tabs for request history
            yearsSince2019() {
            	let years = []
                let dt = new Date()
                //const year = dt.getYear()
                const year = 2022
                for(let i = year; i >= 2019; i--) {
                	years.push(i)
                }
                return years
            }
        },
        methods: {
			// Do a little cleanup when dialog closes
			closeDialog() {
                this.dialog = false
                this.submitResult.complete = false
                this.sendResetPasswordLink = false
                this.$v.userEditable.$reset()
                this.setUser()
            },
            deleteUser() {
				if(confirm('Are you sure you want to delete this user? The user\'s information will still be stored in the database after deletion.')) {
                    this.submitting = true
                    Vue.prototype.$http.delete(`/api/users/${this.user.id}`,
                        {
                            headers: {
                                'Accept': 'application/json',
                                'Authorization': 'Bearer ' + this.apiToken
                            }
                        }
                    )
                    .then(response => {
                        this.submitResult.color = 'success'
                        this.submitResult.msg = 'User was deleted'

                        // Close the dialog and update the parent view by $emit-ing after 2 seconds
                        setTimeout(() => {
                            this.closeDialog()
                            this.$emit('user-deleted')
                        }, 2000)
                    })
                    .catch(e => {
                        this.submitResult.color = 'error'
                        this.submitResult.msg = 'There was a problem deleting the user'
                    })
                    .finally(response => {
                        this.submitResult.complete = true
                        this.submitting = false
                    })
                }
            },
            getRequestHistory() {
                Vue.prototype.$http.get(`/api/requestsbyuser/${this.user.id}`,
                    {
                        headers: {
                            'Accept': 'application/json',
                            'Authorization': 'Bearer ' + this.apiToken
                        }
                    }
                )
                .then(response => {
                    this.pendingRequests = response.data
                    this.pendingRequestsError = false
                })
                .catch(e => {
                    console.log(e)
                    this.pendingRequestsError = true
                })
            },
            pastDecisionColor(decision) {
			    switch(decision) {
                  case 'approved':
                  	return 'success--text'
                  case 'denied':
                  	return 'error--text'
                  default:
                  	return ''
                }
            },
            pastDecisionText(decision) {
                switch(decision) {
                    case 'approved':
                        return 'Approved'
                    case 'denied':
                        return 'Denied'
                    default:
                        return 'No Action'
                }
            },
            requestsForYear(year) {
                let dt = new Date()
                const thisYear = dt.getYear()
                const today = Vue.prototype.$moment().format('YYYY-MM-DD')

                // If current year, get only requests up to this date
                if(year == thisYear) {
                    return this.pendingRequests.filter(pr => {
                        return pr.date_requested >= year + '-01-01' && pr.date_requested <= today
                    })
                }
                return this.pendingRequests.filter(pr => {
                    return pr.date_requested >= year + '-01-01' && pr.date_requested <= year + '-12-31'
                })
            },
            setUser() {
            	this.userEditable = Object.assign({}, this.user)
            },
            updateUser() {
                this.submitting = true
                Vue.prototype.$http.put(`/api/users/${this.user.id}`,
                  {
                  	user: this.userEditable,
                    sendResetPasswordLink: this.sendResetPasswordLink
                  },
                  {
                  	headers: {
                  		'Accept': 'application/json',
                        'Authorization': 'Bearer ' + this.apiToken
                    }
                  }
                )
                .then(response => {
                	this.submitResult.color = 'success'
                    this.submitResult.msg = 'User was updated'

                    // Update the parent view by $emit-ing after 2 seconds
                    setTimeout(() => {
                        this.submitResult.complete = false
                        this.sendResetPasswordLink = false
                        this.$v.userEditable.$reset()
                        this.$emit('user-updated')
                    }, 2000)
                })
                .catch(e => {
                	this.submitResult.color = 'error'
                    this.submitResult.msg = 'There was a problem updating the user'
                })
                .finally(response => {
                	this.submitResult.complete = true
                	this.submitting = false
                })
            }
        },
        watch: {
			dialog() {
				if(this.dialog == true) {
					this.setUser()
                    this.getRequestHistory()
                }
            }
        }
	}
</script>
<style lang="scss" scoped>
    .clickable {
        cursor: pointer;
        text-decoration: underline;
    }
</style>

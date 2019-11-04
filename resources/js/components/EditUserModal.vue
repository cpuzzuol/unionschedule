<template>
    <div class="edit-user-container">
        <v-dialog v-model="dialog" persistent scrollable max-width="590px">
            <template v-slot:activator="{ on }">
                <v-btn color="primary" dark v-on="on" icon><v-icon>mdi-pencil</v-icon></v-btn>
            </template>
            <v-card>
                <v-toolbar dark>
                    <v-toolbar-title>Edit User: {{ user.first_name + ' ' + user.last_name }}</v-toolbar-title>
                    <v-spacer></v-spacer>
                    <v-btn icon dark @click="closeDialog"><v-icon>mdi-close</v-icon></v-btn>
                </v-toolbar>
                <v-card-text style="height: 500px;">
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
        </v-dialog>
    </div>
</template>
<script>
	import Vue from 'vue'
	import Vuelidate from 'vuelidate'
	import { required, helpers, sameAs, numeric } from 'vuelidate/lib/validators'
	Vue.use(Vuelidate)

	const unionSortersEmail = helpers.regex('alpha', /.+@unionsorters\.com$/);
	export default {
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
            submitResult: {
            	color: 'info',
                complete: false,
                msg: ''
            },
            sendResetPasswordLink: false,
            submitting: false,
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

                    // Close the dialog and update the parent view by $emit-ing after 2 seconds
                    setTimeout(() => {
                        this.closeDialog()
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
                }
            }
        }
	}
</script>

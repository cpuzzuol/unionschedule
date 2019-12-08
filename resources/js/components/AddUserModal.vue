<template>
    <div class="add-user-container">
        <v-dialog v-model="dialog" persistent scrollable max-width="590px">
            <template v-slot:activator="{ on }">
                <v-btn title="Add User" color="info" v-on="on"><v-icon>mdi-plus</v-icon><v-icon>mdi-account</v-icon></v-btn>
            </template>
            <v-card>
                <v-toolbar dark>
                    <v-toolbar-title>Add System User</v-toolbar-title>
                    <v-spacer></v-spacer>
                    <v-btn icon dark @click="closeDialog"><v-icon>mdi-close</v-icon></v-btn>
                </v-toolbar>
                <v-card-text style="height: 500px;">
                    <v-form>
                        <v-row>
                            <v-col cols="12" xs="12" sm="6">
                                <v-text-field
                                    v-model="user.first_name"
                                    label="First Name*"
                                    filled
                                    :error-messages="errorsFirstName"
                                    @input="$v.user.first_name.$touch()"
                                >
                                </v-text-field>
                            </v-col>
                            <v-col cols="12" xs="12" sm="6">
                                <v-text-field
                                    v-model="user.last_name"
                                    label="Last Name*"
                                    filled
                                    :error-messages="errorsLastName"
                                    @input="$v.user.last_name.$touch()"
                                >
                                </v-text-field>
                            </v-col>
                            <v-col cols="12">
                                <v-text-field
                                    v-model="user.email"
                                    label="Email*"
                                    filled
                                    :error-messages="errorsEmail"
                                    @input="$v.user.email.$touch()"
                                >
                                </v-text-field>
                            </v-col>
                            <v-col cols="12" xs="12" sm="6">
                                <v-text-field
                                    v-model="user.password"
                                    label="Password*"
                                    :append-icon="showPw1 ? 'mdi-eye' : 'mdi-eye-off'"
                                    :type="showPw1 ? 'text' : 'password'"
                                    filled
                                    @click:append="showPw1 = !showPw1"
                                    :error-messages="errorsPassword"
                                    @input="$v.user.password.$touch()"
                                    hint="Must contain at least 8 characters with at least 1 number and one special character"
                                    persistent-hint
                                >
                                </v-text-field>
                            </v-col>
                            <v-col cols="12" xs="12" sm="6">
                                <v-text-field
                                    v-model="user.password_confirm"
                                    label="Confirm Password*"
                                    :append-icon="showPw2 ? 'mdi-eye' : 'mdi-eye-off'"
                                    :type="showPw2 ? 'text' : 'password'"
                                    filled
                                    @click:append="showPw2 = !showPw2"
                                    :error-messages="errorsPasswordConfirm"
                                    @input="$v.user.password_confirm.$touch()"
                                >
                                </v-text-field>
                            </v-col>
                            <v-col cols="12" xs="12" sm="6">
                                <v-text-field
                                    v-model="user.vacation_days"
                                    label="Vacation Days"
                                    filled
                                    :error-messages="errorsVacationDays"
                                    @input="$v.user.vacation_days.$touch()"
                                >
                                </v-text-field>
                            </v-col>
                            <v-col cols="12" xs="12" md="6">
                                <v-switch
                                    v-model="user.is_admin"
                                    label="Administrator"
                                >
                                </v-switch>
                            </v-col>
                        </v-row>
                    </v-form>
                    <v-alert
                        :color="submitResult.color"
                        :value="submitResult.complete"
                        dark
                    >
                        <template v-if="submitResult.validationErrors">
                            <p>User was not created because of the following validation problems:</p>
                            <ol>
                                <li
                                    v-for="(error, index) in submitResult.validationErrors"
                                    :key="'validation-error-' + index"
                                >
                                    {{ error }}
                                </li>
                            </ol>
                        </template>
                        <template v-else>
                            {{ submitResult.msg }}
                        </template>
                    </v-alert>
                    <p class="error--text">All fields marked with * are required.</p>
                    <p>On creation, the new user will receive an email with their login credentials.</p>
                </v-card-text>
                <v-divider></v-divider>
                <v-card-actions>
                    <v-btn color="success darken-1" :disabled="$v.user.$invalid || submitting" :loading="submitting" @click="createUser">Create User</v-btn>
                    <v-btn color="secondary" :disabled="submitting" outlined @click="resetUser">Reset</v-btn>
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
            }
		},
        validations () {
            return {
                user: {
                    email: {
                    	required,
                        unionSortersEmail
                    },
                    first_name: {
                        required
                    },
                    last_name: {
                        required
                    },
                    password: {
                        strongPassword(password1) {
                            return (
                                /[a-z]/.test(password1) && // checks for a-z
                                /[0-9]/.test(password1) && // checks for 0-9
                                /\W|_/.test(password1) && // checks for special char
                                password1.length >= 8
                            );
                        }
                    },
                    password_confirm: {
                        sameAsPassword: sameAs("password")
                    },
                    vacation_days: {
                    	required,
                    	numeric
                    }
                }
            }
        },
        created() {
        },
		data: () => ({
            dialog: false,
            showPw1: false,
            showPw2: false,
            submitResult: {
            	color: 'info',
                complete: false,
                msg: '',
                validationErrors: null,
            },
            submitting: false,
            user: {
            	first_name: '',
                last_name: '',
                email: '',
                vacation_days: 0,
                is_admin: false,
                password: '',
                password_confirm: '',
            }
		}),
        computed: {
			errorsEmail () {
                const errors = []
                if (!this.$v.user.email.$dirty) { return errors } // clean
                !this.$v.user.email.unionSortersEmail && errors.push('Must be a unionsorters.com email address')
                return errors
            },
            errorsFirstName () {
                const errors = []
                if (!this.$v.user.first_name.$dirty) { return errors } // clean
                !this.$v.user.first_name.required && errors.push('First name is required')
                return errors
            },
            errorsLastName () {
                const errors = []
                if (!this.$v.user.last_name.$dirty) { return errors } // clean
                !this.$v.user.last_name.required && errors.push('Last name is required')
                return errors
            },
            errorsPassword () {
                const errors = []
                if (!this.$v.user.password.$dirty) { return errors } // clean
                !this.$v.user.password.strongPassword && errors.push('Must contain at least 8 characters with at least 1 number and one special character')
                return errors
            },
            errorsPasswordConfirm () {
                const errors = []
                if (!this.$v.user.password_confirm.$dirty) { return errors } // clean
                !this.$v.user.password_confirm.sameAsPassword && errors.push('Must match first password input')
                return errors
            },
            errorsVacationDays () {
                const errors = []
                !this.$v.user.vacation_days.numeric && errors.push('Must be a valid number of days')
                return errors
            }
        },
        methods: {
			// Do a little cleanup when dialog closes
			closeDialog() {
                this.dialog = false
                this.submitResult.complete = false
                this.resetUser()
            },
            createUser() {
                this.submitting = true
                Vue.prototype.$http.post(`/api/users/register`,
                  {
                  	user: this.user
                  },
                  {
                  	headers: {
                  		'Accept': 'application/json',
                        'Authorization': 'Bearer ' + this.apiToken
                    }
                  }
                )
                .then(response => {
                	this.submitResult.validationErrors = null
                	this.submitResult.color = 'success'
                    this.submitResult.msg = 'User was created'

                    // Close the dialog and update the parent view by $emit-ing after 2 seconds
                    setTimeout(() => {
                        this.closeDialog()
                        this.$emit('user-created')
                    }, 2000)
                })
                .catch(e => {
                	this.submitResult.color = 'error'
                    if(e.response.status != '422') {
                        this.submitResult.validationErrors = null
                        this.submitResult.msg = 'There was a problem creating the user'
                    } else {
                        this.submitResult.validationErrors = e.response.data.error
                    }
                })
                .finally(response => {
                	this.submitResult.complete = true
                	this.submitting = false
                })
            },
            resetUser() {
				this.user = {
                    first_name: '',
                    last_name: '',
                    email: '',
                    vacation_days: 0,
                    is_admin: false,
                    password: '',
                    password_confirm: ''
                }
                this.$v.user.$reset() // reset validation
            }
        },
        watch: {
			dialog() {
				if(this.dialog == true) {
					this.resetUser()
                }
            }
        }
	}
</script>

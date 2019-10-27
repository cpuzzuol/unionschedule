<template>
    <div class="edit-user-container">
        <v-dialog v-model="dialog" persistent scrollable max-width="590px">
            <template v-slot:activator="{ on }">
                <v-btn color="primary" dark v-on="on" icon><v-icon>mdi-pencil</v-icon></v-btn>
            </template>
            <v-card>
                <v-toolbar dark>
                    <v-toolbar-title>Edit User: {{ user.name }}</v-toolbar-title>
                    <v-spacer></v-spacer>
                    <v-btn icon dark @click="closeDialog"><v-icon>mdi-close</v-icon></v-btn>
                </v-toolbar>
                <v-card-text style="height: 500px;">
                    <v-form v-model="valid">
                        <v-row>
                            <v-col cols="12">
                                <v-text-field
                                    v-model="userEditable.name"
                                    label="Name"
                                    :rules="nameRules"
                                    filled
                                >
                                </v-text-field>
                                <v-text-field
                                    v-model="userEditable.email"
                                    label="Email"
                                    :rules="emailRules"
                                    filled
                                >
                                </v-text-field>
                            </v-col>
                            <v-col cols="12" xs="12" sm="6">
                                <v-text-field
                                    v-model="userEditable.vacation_days"
                                    label="Vacation Days"
                                    type="number"
                                    :rules="vacationRules"
                                    filled
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
                </v-card-text>
                <v-divider></v-divider>
                <v-card-actions>
                    <v-btn color="success darken-1" :disabled="!valid || submitting" :loading="submitting" @click="updateUser">Update User</v-btn>
                    <v-btn color="secondary" :disabled="submitting" outlined @click="setUser">Reset</v-btn>
                    <v-spacer></v-spacer>
                    <v-btn color="error" :disabled="submitting">Delete User</v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>
    </div>
</template>
<script>
    import Vue from 'vue'
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
		data: () => ({
            dialog: false,
            emailRules: [
                v => !!v || 'Email address is required',
                v => /.+@unionsorters\.com$/.test(v) || 'Email must end in @unionsorters.com'
            ],
            nameRules: [
                v => !!v || 'Name is required',
            ],
            submitResult: {
            	color: 'info',
                complete: false,
                msg: ''
            },
            sendResetPasswordLink: false,
            submitting: false,
            userEditable: [],
            vacationRules: [
                v => /^[0-9]+$/.test(v) || 'Must be a number'
            ],
            valid: false
		}),
        computed: {

        },
        methods: {
			// Do a little cleanup when dialog closes
			closeDialog() {
                this.dialog = false
                this.submitResult.complete = false
                this.sendResetPasswordLink = false
                this.setUser()
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

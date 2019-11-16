<template>
    <div>
        <template v-if="dataLoading">
            <v-layout row justify-center align-center :class="className">
                <v-progress-circular indeterminate color="primary" :size="spinnerSize"></v-progress-circular>
            </v-layout>
            <v-layout v-if="loadingMessage != ''" row justify-center align-center>{{loadingMessage}}</v-layout>
        </template>
        <template v-else>
            <slot></slot>
        </template>
    </div>
</template>
<script>
	import Vue from 'vue'

	export default {
        props: {
            dataLoading: {
                type: Boolean,
                required: true
            },
            className: {
                type: String,
                required: false,
                default: 'reg-loader'
            },
            loadingMessage: {
                type: String,
                required: false,
                default: ''
            },
            spinnerSize: {
                type: Number,
                default: 70
            }
        },
        created() {
        },
		data: () => ({
            dialog: false,
            note: '',
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
                        return {icon: 'Deny', color: 'error darken-1'}
                    case 'pending':
                        return {icon: 'Set Pending', color: 'warning darken-1'}
                    default:
                        return
                }
            },
			iconProps() {
            	switch(this.action) {
                  case 'approve':
                  	return {icon: 'mdi-thumb-up', color: 'success darken-1', title: 'Approve Vacation Request'}
                  case 'deny':
                    return {icon: 'mdi-thumb-down', color: 'error darken-1', title: 'Deny Vacation Request'}
                  case 'pending':
                  	return {icon: 'mdi-account-clock', color: 'warning darken-1', title: 'Set Vacation Request to Pending'}
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
            }
        }
	}
</script>

<style scoped lang="scss">
    .tall-loader {
        height: 500px;
        padding: 200px 0;
    }

    .med-loader {
        height: 300px;
        padding: 100px 0;
    }

    .reg-loader {
        padding: 50px 0;
    }
</style>

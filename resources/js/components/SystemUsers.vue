<template>
    <div class="system-users-container">
        <v-row>
            <v-col cols="12">
                <AddUserModal
                    :api-token="user.api_token"
                    @user-created="getUsers"
                ></AddUserModal>
            </v-col>
            <v-col cols="12">
                <v-data-table
                    :headers="headers"
                    :items="users"
                    :items-per-page="25"
                    :loading="dataLoading"
                    :footer-props="{
                       'items-per-page-options': [5, 10, 25, 50, 75, 100]
                    }"
                    class="elevation-1"
                >
                    <template v-slot:item.last_name="{ item }">
                        <EditUserModal
                            :api-token="user.api_token"
                            :user="item"
                            :can-edit-admin-setting="item.email != user.email"
                            @user-updated="getUsers"
                            @user-deleted="getUsers"
                        >
                            {{ item.last_name + ', ' + item.first_name }}
                            <v-icon v-if="item.is_admin" color="warning"right>mdi-account-star</v-icon>
                        </EditUserModal>
                    </template>
                </v-data-table>
            </v-col>
        </v-row>
    </div>
</template>
<script>
    import Vue from 'vue'
    import EditUserModal from "./EditUserModal"
    import AddUserModal from "./AddUserModal"
	export default {
		props: {
			user: {
                type: Object,
                required: true
            }
		},
        components: { EditUserModal, AddUserModal },
        created() {
			this.getUsers()
        },
		data: () => ({
            dataLoading: false,
            users: []
		}),
        computed: {
			headers() {
                return [
                	{ text: 'Name', value: 'last_name' },
                    { text: 'Email', value: 'email' },
                    { text: 'Pending Requests', value: 'outstanding_requests'},
                    { text: 'PTO Bank (Days)', value: 'vacation_days' }
                ]
            }
        },
        methods: {
            getUsers() {
                this.dataLoading = true
                Vue.prototype.$http.get('/api/users',
                  {
                  	headers: {
                  		'Accept': 'application/json',
                        'Authorization': 'Bearer ' + this.user.api_token
                    }
                  }
                )
                .then(response => {
                    this.users = response.data
                })
                .catch(e => {
                	console.log(e)
                })
                .finally(e => {
                	this.dataLoading = false
                })
            },
            submit() {
				// this.submitting = true
				// Vue.prototype.$http.post('/api/vacationrequests',
                //   {
                //   	requestedDates: this.dates,
                //     userID: this.user.id
                //   },
                //   {
                //   	headers: {
                //   		'Accept': 'application/json',
                //         'Authorization': 'Bearer ' + this.user.api_token
                //     }
                //   }
                // )
                // .then(response => {
                // 	console.log(response.data)
                //     this.submitting = false
                // })
                // .catch(e => {
                // 	console.log(e)
                // })
            }
        },
        watch: {
        }
	}
</script>

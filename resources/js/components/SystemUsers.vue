<template>
    <div class="system-users-container">
        <v-row>
            <v-col cols="12">
                <v-data-table
                    :headers="headers"
                    :items="users"
                    :items-per-page="25"
                    :loading="dataLoading"
                    class="elevation-1"
                >
                    <template v-slot:item.name="{ item }">
                        <v-chip
                            class="ma-2"
                            :color="item.is_admin ? 'orange' : 'info'"
                            text-color="white"
                        >
                            {{ item.name }}
                            <v-icon right>{{ item.is_admin ? 'mdi-star' : 'mdi-account-circle' }}</v-icon>
                        </v-chip>
                    </template>
                    <template v-slot:item.id="{ item }">
                        <EditUserModal
                            :api-token="user.api_token"
                            :user="item"
                            :can-edit-admin-setting="item.email != user.email"
                            @user-updated="getUsers"
                        ></EditUserModal>
                    </template>
                </v-data-table>
            </v-col>
        </v-row>
    </div>
</template>
<script>
    import Vue from 'vue'
    import EditUserModal from "./EditUserModal";
	export default {
		props: {
			user: {
                type: Object,
                required: true
            }
		},
        components: { EditUserModal },
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
                	{ text: 'Name', value: 'name' },
                    { text: 'Email', value: 'email' },
                    { text: 'Vacation Days', value: 'vacation_days' },
                    { text: 'Actions', value: 'id', sortable: false }
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
                	// console.log(response.data)
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

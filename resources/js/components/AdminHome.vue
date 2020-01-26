<template>
    <div class="admin-home-container">
        <data-loading :data-loading="loading">
            <v-row>
                <v-col
                    cols="12"
                    xs="12"
                    sm="6"
                    md="4"
                >
                    <v-card>
                        <v-card-title>Pending Requests</v-card-title>
                        <v-card-text>
                            <span class="error--text"><strong>{{ outstandingRequests.length }}</strong></span> Requests require attention<br>
                            <admin-pending-requests-modal :pending-requests="outstandingRequests" :user="user" @request-updated="getData"></admin-pending-requests-modal>
                        </v-card-text>
                    </v-card>
                </v-col>
                <v-col
                    cols="12"
                    xs="12"
                    sm="6"
                    md="4"
                >
                    <v-card>
                        <v-card-title>Restricted Dates</v-card-title>
                        <v-card-text>
                            <span class="error--text"><strong>{{ restrictedDates.length }}</strong></span> Dates are restricted from being requested<br>
                            <admin-restricted-dates-modal :all-vacation-requests="allVacationRequests" :restricted-dates="restrictedDates" :user="user" @restriction-updated="getData"></admin-restricted-dates-modal>
                        </v-card-text>
                    </v-card>
                </v-col>
                <v-col
                    cols="12"
                    xs="12"
                    sm="6"
                    md="4"
                >
                    <v-card>
                        <v-card-title>User Management</v-card-title>
                        <v-card-text>
                            Add users, view requests and logs by user, etc.<br>
                            <v-btn color="info" text href="admin/users">Go To Page</v-btn>
                        </v-card-text>
                    </v-card>
                </v-col>
                <v-col
                    cols="12"
                    xs="12"
                    sm="12"
                >
                    <v-card>
                        <v-card-title>PTO Overview</v-card-title>
                        <v-card-text>
                            <p>Click on a date to view the PTO requests for that date.</p>
                            <admin-overview-calendar :restricted-dates="restrictedDates" :user="user" @request-updated="getData"></admin-overview-calendar>
                        </v-card-text>
                    </v-card>
                </v-col>
            </v-row>
        </data-loading>
    </div>
</template>
<script>
    import Vue from 'vue'
    import AdminPendingRequestsModal from "./AdminPendingRequestsModal"
    import AdminOverviewCalendar from "./AdminOverviewCalendar"
    import AdminRestrictedDatesModal from "./AdminRestrictedDatesModal"
    import DataLoading from "./DataLoading"

	export default {
        components: { DataLoading, AdminOverviewCalendar, AdminPendingRequestsModal, AdminRestrictedDatesModal },
        props: {
            user: {
        		type: Object,
                required: true
            }
		},
        created() {
            this.loading = true
            this.loadError = false
            this.getData()
        },
		data: () => ({
          allVacationRequests: [],
          loading: false,
          loadError: false,
          outstandingRequests: [],
          restrictedDates: []
		}),
        computed: {

        },
        methods: {
        	getData() {
                Vue.prototype.$http.get('/api/admin/homedata',
                {
                    headers: {
                        'Accept': 'application/json',
                        'Authorization': 'Bearer ' + this.user.api_token
                    }
                })
                .then(response => {
                	this.outstandingRequests = response.data.outstandingRequests
                    this.restrictedDates = response.data.restrictedDates
                    this.allVacationRequests = response.data.allVacationRequests
                })
                .catch(e => {
                	this.loadError = true
                    console.log(e)
                })
                .finally(response => {
                	this.loading = false
                })
            }
        },
        watch: {
        }
	}
</script>

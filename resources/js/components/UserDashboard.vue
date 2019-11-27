<template>
    <div class="user-dashboard-container">
        <template v-if="outstandingRequestsError">
            <v-alert :value="true" type="error">
                There was a problem loading your vacation requests.
            </v-alert>
        </template>
        <template v-else>
            <v-row>
                <v-col cols="12" xs="12">
                    You have <strong>{{ userDaysLeft }}</strong> vacation days left this year.<br><v-btn color="info" outlined href="/vacation-request">New Vacation Request</v-btn>
                </v-col>
                <v-col
                    xs="12"
                    sm="12"
                    md="6"
                >
                    <v-card outlined>
                        <v-card-title class="title">Future Pending Requests</v-card-title>
                        <v-card-text>
                            <user-dashboard-outstanding-requests :user="user" :outstanding-requests="futureOutstandingRequests" @request-canceled="handleRequestCanceled"></user-dashboard-outstanding-requests>
                        </v-card-text>
                    </v-card>
                </v-col>
                <v-col
                    xs="12"
                    sm="12"
                    md="6"
                >
                    <v-card outlined>
                        <v-card-title class="title">Past Vacation Requests</v-card-title>
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
                                <v-tab v-for="year in yearsSince2019" :key="'past-req-tab-' + year">{{ year }}</v-tab>
                                <v-tabs-items v-model="pastRequestsTab">
                                    <v-tab-item
                                        v-for="year in yearsSince2019"
                                        :key="'past-req-tab-content-' + year"
                                    >
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
    </div>
</template>
<script>
	import Vue from 'vue'
    import UserDashboardOutstandingRequests from "./UserDashboardOutstandingRequests";
    import SystemUserVacationRequestLogModal from "./SystemUserVacationRequestLogModal";

	export default {
        components: { SystemUserVacationRequestLogModal, UserDashboardOutstandingRequests  },
        props: {
            user: {
                type: Object,
                required: true
            }
		},
        created() {
        	this.userDaysLeft = this.user.vacation_days
            this.getRequestHistory()
        },
		data: () => ({
            pastRequestsTab: null,
            outstandingRequests: [],
            outstandingRequestsError: false,
            sendResetPasswordLink: false,
            userDaysLeft: 0,
		}),
        computed: {
            futureOutstandingRequests() {
                const today = Vue.prototype.$moment().format('YYYY-MM-DD')
                return this.outstandingRequests.filter(pr => {
                    return pr.date_requested >= today
                })
            },
            // Used for years tabs for request history
            yearsSince2019() {
            	let years = []
                let dt = new Date()
                const year = dt.getFullYear()
                for(let i = year; i >= 2019; i--) {
                	years.push(i)
                }
                return years
            }
        },
        methods: {
            getRequestHistory() {
                Vue.prototype.$http.get(`/api/requestsbyuser/${this.user.id}`,
                    {
                        headers: {
                            'Accept': 'application/json',
                            'Authorization': 'Bearer ' + this.user.api_token
                        }
                    }
                )
                .then(response => {
                    this.outstandingRequests = response.data
                    this.outstandingRequestsError = false
                })
                .catch(e => {
                    console.log(e)
                    this.outstandingRequestsError = true
                })
            },
            getUserDaysLeft() {
                Vue.prototype.$http.get(`/api/userdaysleft/${this.user.id}`,
                    {
                        headers: {
                            'Accept': 'application/json',
                            'Authorization': 'Bearer ' + this.user.api_token
                        }
                    }
                )
                .then(response => {
                    this.userDaysLeft = response.data
                })
                .catch(e => {
                    console.log(e)
                })
            },
            handleRequestCanceled() {
                this.getRequestHistory()
                this.getUserDaysLeft()
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
                const thisYear = dt.getFullYear()
                const today = Vue.prototype.$moment().format('YYYY-MM-DD')

                // If current year, get only requests up to this date
                if(year == thisYear) {
                    return this.outstandingRequests.filter(pr => {
                        return pr.date_requested >= year + '-01-01' && pr.date_requested <= today
                    })
                }
                return this.outstandingRequests.filter(pr => {
                    return pr.date_requested >= year + '-01-01' && pr.date_requested <= year + '-12-31'
                })
            },
        },
        watch: {
        }
	}
</script>
<style lang="scss" scoped>
    .clickable {
        cursor: pointer;
        text-decoration: underline;
    }
</style>

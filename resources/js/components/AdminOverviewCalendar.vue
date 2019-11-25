<template>
    <div class="admin-overview-calendar-container">
        <v-row>
            <v-col cols="12" sm="6" lg="4">
                <v-date-picker
                    v-model="date"
                    full-width
                    :max="maxDate"
                    :allowed-dates="allowedDates"
                    @change="getRequestsByDate"
                ></v-date-picker>
            </v-col>
            <v-col cols="12" sm="6" lg="8">
                <v-row>
                    <v-col v-if="loading" cols="12" class="text-center">
                        <v-progress-circular indeterminate color="green"></v-progress-circular>
                    </v-col>
                    <v-col v-else-if="loadingError" cols="12" class="text-center">
                        <v-alert type="error">There was a error retrieving the requests.</v-alert>
                    </v-col>
                    <v-col v-else cols="12">
                        <v-alert v-if="isRestrictedDate" type="warning">Managers are restricted from booking on this date.</v-alert>
                        <v-alert v-if="dateRequests.length == 0" type="info">No vacation requests on this date.</v-alert>
                        <template v-else>
                            <v-card>
                                <v-card-title class="title success--text">{{ vacationRequestsApproved.length }} Approved</v-card-title>
                                <v-card-text v-if="vacationRequestsApproved.length > 0">
                                    <v-list dense>
                                        <v-list-item v-for="(req, index) in vacationRequestsApproved" :key="'req-approved-' + index">
                                            <v-list-item-content>
                                                {{ req.requester.first_name + ' ' + req.requester.last_name }}
                                            </v-list-item-content>
                                            <v-list-item-icon>
                                                <admin-manage-vacation-request-modal action="pending" :user="user" :vacation-request="req" @request-updated="$emit('request-updated')"></admin-manage-vacation-request-modal>
                                                <admin-manage-vacation-request-modal action="deny" :user="user" :vacation-request="req" @request-updated="$emit('request-updated')"></admin-manage-vacation-request-modal>
                                            </v-list-item-icon>
                                        </v-list-item>
                                    </v-list>
                                </v-card-text>
                            </v-card>
                            <v-card class="mt-2">
                                <v-card-title class="title warning--text">{{ vacationRequestsPending.length }} Pending</v-card-title>
                                <v-card-text v-if="vacationRequestsPending.length > 0">
                                    <v-list dense>
                                        <v-list-item v-for="(req, index) in vacationRequestsPending" :key="'req-pending-' + index">
                                            <v-list-item-content>
                                                {{ req.requester.first_name + ' ' + req.requester.last_name }}
                                            </v-list-item-content>
                                            <v-list-item-icon>
                                                <admin-manage-vacation-request-modal action="approve" :user="user" :vacation-request="req" @request-updated="$emit('request-updated')"></admin-manage-vacation-request-modal>
                                                <admin-manage-vacation-request-modal action="deny" :user="user" :vacation-request="req" @request-updated="$emit('request-updated')"></admin-manage-vacation-request-modal>
                                            </v-list-item-icon>
                                        </v-list-item>
                                    </v-list>
                                </v-card-text>
                            </v-card>
                            <v-card class="mt-2">
                                <v-card-title class="title error--text">{{ vacationRequestsDenied.length }} Denied</v-card-title>
                                <v-card-text v-if="vacationRequestsDenied.length > 0">
                                    <v-list dense>
                                        <v-list-item v-for="(req, index) in vacationRequestsDenied" :key="'req-denied-' + index">
                                            <v-list-item-content>
                                                {{ req.requester.first_name + ' ' + req.requester.last_name }}
                                            </v-list-item-content>
                                            <v-list-item-icon>
                                                <admin-manage-vacation-request-modal action="pending" :user="user" :vacation-request="req" @request-updated="$emit('request-updated')"></admin-manage-vacation-request-modal>
                                                <admin-manage-vacation-request-modal action="approve" :user="user" :vacation-request="req" @request-updated="$emit('request-updated')"></admin-manage-vacation-request-modal>
                                            </v-list-item-icon>
                                        </v-list-item>
                                    </v-list>
                                </v-card-text>
                            </v-card>
                        </template>
                    </v-col>
                </v-row>
            </v-col>
        </v-row>
    </div>
</template>
<script>
    import Vue from 'vue'
    import AdminManageVacationRequestModal from "./AdminManageVacationRequestModal"
	export default {
        components: { AdminManageVacationRequestModal },
        props: {
        restrictedDates: {
            type: Array,
            required: true
        },
        user: {
            type: Object,
            required: true
        }
		},
        created() {
			this.getRequestsByDate()
        },
		data: () => ({
            date: Vue.prototype.$moment().format('YYYY-MM-DD'),
            dateRequests: [],
            loading: true,
            loadingError: false,
            menu: false,
            //minDate: Vue.prototype.$moment().format('YYYY-MM-DD'),
            maxDate: Vue.prototype.$moment().endOf('year').format('YYYY-MM-DD'),
            serverStatus: null,
            submitting: false
		}),
        computed: {
        	isRestrictedDate() {
        		return this.restrictedDates.find(rd => {
        			return rd.date == this.date
                })
            },
			vacationRequestsApproved() {
                return this.dateRequests.filter(dr => {
                	return dr.decision == 'approved'
                })
			},
            vacationRequestsDenied() {
                return this.dateRequests.filter(dr => {
                    return dr.decision == 'denied'
                })
            },
            vacationRequestsPending() {
                return this.dateRequests.filter(dr => {
                    return dr.decision == 'pending'
                })
            }
        },
        methods: {
            allowedDates(val) {
                const dt = Vue.prototype.$moment(val, 'YYYY-MM-DD')
            	// Weekends not allowed
                if(dt.weekday() == 0 || dt.weekday() == 6) {
                	return false
                }
                // Loop through restricted dates
                let requestable = true
                // this.restrictedDates.forEach(rd => {
                // 	if(rd.date == val) {
                //         requestable = false
                //     }
                // })
                return requestable
            },
            getRequestsByDate() {
                this.loading = true
                Vue.prototype.$http.get(`/api/vacationrequestsbydate/${this.date}`,
                    {
                        headers: {
                            'Accept': 'application/json',
                            'Authorization': 'Bearer ' + this.user.api_token
                        }
                    }
                )
                .then(response => {
                    this.dateRequests = response.data
                    this.loadingError = false
                })
                .catch(e => {
                    console.log(e)
                    this.loadingError = true
                })
                .finally(() => {
                    this.loading = false
                })
            },
            submit() {
				this.submitting = true
				Vue.prototype.$http.post('/api/vacationrequests',
                  {
                  	requestedDates: this.dates,
                    userID: this.user.id
                  },
                  {
                  	headers: {
                  		'Accept': 'application/json',
                        'Authorization': 'Bearer ' + this.user.api_token
                    }
                  }
                )
                .then(response => {
                	console.log(response.data)
                    //this.submitting = false
                    this.serverStatus = 200
                    setTimeout(() => {
                    	location.reload()
                    }, 2500)
                })
                .catch(e => {
                	console.log(e)
                    this.submitting = false
                    this.serverStatus = e.response.status
                })
            }
        },
        watch: {
            restrictedDates() {
            	this.getRequestsByDate()
            }
        }
	}
</script>

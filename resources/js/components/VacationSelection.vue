<template>
    <div class="vacation-container">
        <v-btn color="info" outlined href="/dashboard" title="Back to Dashboard"><v-icon>mdi-arrow-left</v-icon><v-icon>mdi-view-dashboard</v-icon></v-btn>
        <data-loading v-if="!loadingData">
            <v-row>
                <v-col cols="12" sm="6" lg="4">
                    <v-date-picker
                        v-model="dates"
                        multiple
                        full-width
                        :min="minDate"
                        :max="maxDate"
                        :allowed-dates="allowedDates"
                        :events="previousRequestMarkers"
                        :event-color="previousRequestMarkerColor"
                    ></v-date-picker>
                    <p>
                        <span class="success--text">&#x25CF;</span> = request approved on this date.<br>
                        <span class="error--text">&#x25CF;</span> = request denied on this date.<br>
                        <span class="warning--text">&#x25CF;</span> = request pending on this date.
                    </p>
                </v-col>
                <v-col cols="12" sm="6" lg="8">
                    <v-card
                        class="mx-auto"
                        shaped
                    >
                        <v-card-title :class="{ 'error--text': daysLeft == 0, 'info--text': daysLeft > 0 }">{{ daysLeft }} PTO days left this year</v-card-title>
                        <v-card-text>
                            <p v-if="dates.length == 0">Select one or multiple dates from the picker...</p>
                            <p v-else><strong>Your selected dates:</strong></p>
                            <v-chip
                                v-for="(dt, index) in dates"
                                :key="'selected-date-' + index"
                                class="mr-1 mb-2"
                                close
                                color="info"
                                text-color="white"
                                @click:close="removeDate(dt)"
                            >
                                {{ dt | slashdate }}
                            </v-chip>
                            <template v-if="serverStatus">
                                <v-alert
                                    dense :type="submissionFormatting.color"
                                >{{ submissionFormatting.msg }}</v-alert>
                            </template>
                        </v-card-text>
                        <v-divider></v-divider>
                        <v-card-actions v-if="serverStatus != 200">
                            <v-btn color="success" :disabled="dates.length == 0" :loading="submitting" @click="submit">Submit for review</v-btn>
                            <v-btn color="secondary" outlined :disabled="dates.length == 0 || submitting" @click="clearDates">Reset</v-btn>
                        </v-card-actions>
                    </v-card>
                    <v-alert class="mt-3" type="info" outlined>
                        Submitting this vacation request does not guarantee approval. You will be notified by email when a decision has been made. Contact your supervisor if you believe your vacation balance is incorrect.
                    </v-alert>
                </v-col>
            </v-row>
        </data-loading>
    </div>
</template>
<script>
    import Vue from 'vue'
    import DataLoading from "./DataLoading";
	export default {
			components: { DataLoading },
			props: {
            user: {
				type: Object,
                required: true
            }
		},
        created() {
			this.loadingData = true
			this.getData()
        },
		data: () => ({
            dates: [],
            loadingData: false,
            loadingDataError: false,
            menu: false,
            minDate: Vue.prototype.$moment().startOf('year').format('YYYY-MM-DD'),
            maxDate: Vue.prototype.$moment().endOf('year').format('YYYY-MM-DD'),
            previousRequests: [],
            restrictedDates: [],
            serverStatus: null,
            submitting: false,
            userDaysLeft: 0
		}),
        computed: {
            daysLeft() {
                return parseInt(this.userDaysLeft) - this.dates.length
            },
            previousRequestMarkers() {
				let datesWithMarkers = []
                this.previousRequests.forEach(pr => {
                	datesWithMarkers.push(pr.date_requested)
                })
				return datesWithMarkers
            },
            submissionFormatting() {
                switch(this.serverStatus) {
                    case 200:
                        return {msg: 'You request has been submitted.', color: 'success'}
                    case 422:
                        return {msg: 'This date has already been requested', color: 'error'}
                    case 500:
                        return {msg: 'An unexpected error occurred.', color: 'error'}
                    default:
                        return {msg: null, color: 'dark'}
                }
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
                this.restrictedDates.forEach(rd => {
                	if(rd.date == val) {
                        requestable = false
                    }
                })
                // Loop through previously-requested dates
                this.previousRequests.forEach(pr => {
                	if(pr.date_requested == val) {
                		requestable = false
                    }
                })
                return requestable
            },
			clearDates() {
				this.dates = []
                this.serverStatus = null
            },
            getData() {
                Vue.prototype.$http.get(`/api/users/${this.user.id}`,
                    {
                        headers: {
                            'Accept': 'application/json',
                            'Authorization': 'Bearer ' + this.user.api_token
                        }
                    }
                )
                .then(response => {
                    this.previousRequests = response.data.previousRequests
                    this.restrictedDates = response.data.restrictedDates
                    this.userDaysLeft = response.data.userDaysLeft
                    this.loadingDataError = false
                })
                .catch(e => {
                    console.log(e)
                    this.loadingDataError = true
                })
                .finally(() => {
                	this.loadingData = false
                })
            },
            // Based on the user's previous requests, return a different event color for the date picker
            previousRequestMarkerColor(val) {
                const matchingDate = this.previousRequests.find(pr => {
                	return pr.date_requested == val
                })
                if(!matchingDate) return false
                if(matchingDate.decision == 'approved') {
                	return 'success'
                } else if ( matchingDate.decision == 'denied' ) {
                	return 'error'
                }
                return 'warning'
            },
			removeDate(date) {
				this.dates.splice(this.dates.indexOf(date), 1)
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
                    this.serverStatus = 200
                    setTimeout(() => {
                    	this.submitting = false
                        this.serverStatus = ''
                        this.dates = []
                    	this.getData() // refresh data
                    }, 2000)
                })
                .catch(e => {
                	console.log(e)
                    this.submitting = false
                    this.serverStatus = e.response.status
                })
            }
        },
        watch: {
			// Do not add dates to the array of selected dates if the employees run out of days
			dates() {
				if(this.daysLeft < 0) {
					this.dates.splice(this.dates.length - 1, 1)
                }
            }
        }
	}
</script>

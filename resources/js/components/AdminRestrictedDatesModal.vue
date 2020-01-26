<template>
    <div class="admin-restricted-dates-modal-container">
        <v-dialog v-model="dialog" persistent scrollable max-width="590px">
            <template v-slot:activator="{ on }">
                <v-btn color="info" text v-on="on">Manage</v-btn>
            </template>
            <v-card>
                <v-toolbar dark>
                    <v-toolbar-title>Restricted Dates Management</v-toolbar-title>
                    <v-spacer></v-spacer>
                    <v-btn icon dark @click="closeDialog"><v-icon>mdi-close</v-icon></v-btn>
                </v-toolbar>
                <v-card-text class="mt-4">
                    <v-list dense>
                        <v-subheader>{{ restrictedDates.length }} Restricted Dates This Year</v-subheader>
                        <v-list-item v-for="(dt, index) in restrictedDates" :key="'req-pending-attention-' + index">
                            <v-list-item-content>{{ dt.date | slashdatedow }}</v-list-item-content>
                            <v-list-item-icon v-if="isFutureDate(dt.date)">
                                <v-btn @click="removeRestriction(dt)" color="error" icon><v-icon>mdi-delete</v-icon></v-btn>
                            </v-list-item-icon>
                        </v-list-item>
                    </v-list>
                    <v-divider></v-divider>
                    <v-row>
                        <v-col cols="12">
                            <v-date-picker
                                v-model="newRestrictedDates"
                                multiple
                                full-width
                                :min="minDate"
                                :max="maxDate"
                                :allowed-dates="allowedDates"
                                :events="datesWithExistingRequests"
                                event-color="warning"
                                @input="toggleNewRestriction"
                            ></v-date-picker>
                            <p>
                                <span class="warning--text">&#x25CF;</span> = PTO requests submitted for this date.
                            </p>
                        </v-col>
                        <v-col cols="12">
                            <v-card
                                class="mx-auto"
                                shaped
                            >
                                <v-card-title>New restricted dates</v-card-title>
                                <v-card-text>
                                    <p v-if="newRestrictedDates.length == 0">Select one or multiple dates from the picker...</p>
                                    <p v-else><strong>Selected dates:</strong></p>
                                    <v-chip
                                        v-for="(dt, index) in newRestrictedDates"
                                        :key="'selected-date-' + index"
                                        class="mr-1 mb-2"
                                        close
                                        color="info"
                                        text-color="white"
                                        @click:close="removeNewRestriction(dt)"
                                    >
                                        {{ dt | slashdate }}
                                    </v-chip>
                                    <template v-if="showExistingRequestOptions">
                                        <v-row>
                                            <v-col cols="12">
                                                <p>One or more dates you are trying to restrict have approved or pending PTO requests. Choose what you would like to do with those requests:</p>
                                                <v-radio-group v-model="bulkActions">
                                                    <v-radio label="Do Nothing" value="nothing"></v-radio>
                                                    <v-radio label="Deny Pending Only" value="denyPending"></v-radio>
                                                    <v-radio label="Approve Pending Only" value="approvePending"></v-radio>
                                                    <v-radio label="Deny All" value="denyAll"></v-radio>
                                                </v-radio-group>
                                                <v-switch v-model="notifyOfBulk" label="Email affected people"></v-switch>
                                            </v-col>
                                        </v-row>
                                    </template>
                                    <template v-if="serverStatus">
                                        <v-alert
                                            dense :type="submissionFormatting.color"
                                        >{{ submissionFormatting.msg }}</v-alert>
                                    </template>
                                </v-card-text>
                                <v-divider></v-divider>
                                <v-card-actions>
                                    <v-btn color="success" :disabled="newRestrictedDates.length == 0" :loading="submitting" @click="submitRestrictions">Submit restrictions</v-btn>
                                    <v-btn color="secondary" outlined :disabled="newRestrictedDates.length == 0 || submitting" @click="clearDates">Reset</v-btn>
                                </v-card-actions>
                            </v-card>
                        </v-col>
                    </v-row>
                </v-card-text>
            </v-card>
        </v-dialog>
    </div>
</template>
<script>
	import Vue from 'vue'
    import AdminManageVacationRequestModal from "./AdminManageVacationRequestModal"
    export default {
        components: { },
		props: {
        	allVacationRequests: {
        	    type: Array,
                required: true
            },
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
        },
		data: () => ({
            bulkActions: 'nothing',
            dialog: false,
            minDate: Vue.prototype.$moment().startOf('year').format('YYYY-MM-DD'),
            maxDate: Vue.prototype.$moment().endOf('year').format('YYYY-MM-DD'),
            newRestrictedDates: [],
            notifyOfBulk: true,
            showExistingRequestOptions: false,
            serverStatus: null,
            submitting: false
		}),
        computed: {
        	// Of the unrestricted dates, return any that have vacation requests associated with them
            datesWithExistingRequests() {
                let datesWithMarkers = []
                this.allVacationRequests.forEach(vr => {
                    datesWithMarkers.push(vr.date_requested)
                })
                return datesWithMarkers
            },
            submissionFormatting() {
                switch(this.serverStatus) {
                    case 200:
                        return {msg: 'Restricted dates have been set.', color: 'success'}
                    case 422:
                        return {msg: 'This date has already been restricted', color: 'error'}
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
                return requestable
            },
			clearDates() {
			    this.newRestrictedDates = [],
                this.showExistingRequestOptions = false
            },
        	// Do a little cleanup when dialog closes
			closeDialog() {
                this.dialog = false
            },
            // Check to see if there are existing vacation requests for any dates selected as new restrictions.
            doRequestsExistForDates() {
            	let existingRequests = false
            	this.newRestrictedDates.forEach(rd => {
            		let match = this.allVacationRequests.find(ar => {
            			return ar.date_requested == rd
                    })
                    if(match) {
                        existingRequests = true
                    }
                })
			    return existingRequests
            },
            isFutureDate(date) {
                const today = Vue.prototype.$moment()
                const dateInQuestion = Vue.prototype.$moment(date, 'YYYY-MM-DD')

                return dateInQuestion.isAfter(today)
            },
            toggleNewRestriction() {
                this.showExistingRequestOptions = this.doRequestsExistForDates()
            },
            removeNewRestriction(date) {
                this.newRestrictedDates.splice(this.newRestrictedDates.indexOf(date), 1) // this needs to be first
                this.showExistingRequestOptions = this.doRequestsExistForDates()
            },
            removeRestriction(dateObj) {
				if(confirm(`Are you sure you want to remove ${this.$options.filters.slashdatedow(dateObj.date)} from the list of rectricted dates?`)){
                    Vue.prototype.$http.delete(`/api/restricteddates/${dateObj.date}`,
                        {
                            headers: {
                                'Accept': 'application/json',
                                'Authorization': 'Bearer ' + this.user.api_token
                            }
                        }
                    )
                    .then(response => {
                        this.$emit('restriction-updated')
                    })
                    .catch(e => {
                        console.log(e)
                    })
                }
            },
            submitRestrictions() {
            	this.submitting = true
                Vue.prototype.$http.post('/api/restricteddates',
                    {
                        newRestrictedDates: this.newRestrictedDates,
                        bulkActions: this.bulkActions,
                        notifyOfBulk: this.notifyOfBulk
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
                    this.serverStatus = 200
                    setTimeout(() => {
                    	this.serverStatus = ''
                        this.clearDates()
                    }, 2500)
                    this.$emit('restriction-updated')
                })
                .catch(e => {
                    console.log(e)
                    this.serverStatus = e.response.status
                })
                .finally(() => {
                    this.submitting = false
                })
            }
        },
        watch: {
        }
	}
</script>

<template>
    <div class="vacation-container">
        <v-row>
            <v-col cols="12" sm="6" lg="4">
                <v-date-picker
                    v-model="dates"
                    multiple
                    :min="minDate"
                    :max="maxDate"
                ></v-date-picker>
            </v-col>
            <v-col cols="12" sm="6" lg="8">
                <v-card
                    class="mx-auto"
                    shaped
                >
                    <v-card-title :class="{ 'error--text': daysLeft == 0, 'info--text': daysLeft > 0 }">{{ daysLeft }} vacation days left this year</v-card-title>
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
                    </v-card-text>
                    <v-divider></v-divider>
                    <v-card-actions>
                        <v-btn color="success" :disabled="dates.length == 0" :loading="submitting" @click="submit">Submit for review</v-btn>
                        <v-btn color="secondary" outlined :disabled="dates.length == 0 || submitting" @click="clearDates">Reset</v-btn>
                    </v-card-actions>
                </v-card>
                <v-alert class="mt-3" type="info" outlined>
                    Submitting this vacation request does not guarantee approval. You will be notified by email when a decision has been made. Contact your supervisor if you believe your vacation balance is incorrect.
                </v-alert>
            </v-col>
        </v-row>
    </div>
</template>
<script>
    import Vue from 'vue'
	export default {
		props: {
            remainingDays: {
            	type: Number|String,
                required: true
            }
		},
        created() {},
		data: () => ({
            dates: [],
            menu: false,
            minDate: Vue.prototype.$moment().add(1, 'days').format('YYYY-MM-DD'),
            maxDate: Vue.prototype.$moment().endOf('year').format('YYYY-MM-DD'),
            submitting: false
		}),
        computed: {
			daysLeft() {
				return parseInt(this.remainingDays) - this.dates.length
            }
        },
        methods: {
			clearDates() {
				this.dates = []
            },
			removeDate(date) {
				this.dates.splice(this.dates.indexOf(date), 1)
            },
            submit() {
				this.submitting = true
				Vue.prototype.$http.post('/api/request', this.dates)
                .then(response => {
                	console.log(response.data)
                    this.submitting = false
                })
                .catch(e => {
                	console.log(e)
                })
            }
        },
        watch: {
			// Do not add dates to the array of selected dates if the employees run out of days
			dates() {
				if(this.daysLeft < 0) {
					console.log("TOO MUCH!")
					this.dates.splice(this.dates.length - 1, 1)
                }
            }
        }
	}
</script>

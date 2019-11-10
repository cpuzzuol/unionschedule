<template>
    <div class="admin-manage-vacation-request-modal-container">
        <v-dialog v-model="dialog" persistent scrollable max-width="590px">
            <template v-slot:activator="{ on }">
                <v-btn color="info" text v-on="on">View</v-btn>
            </template>
            <v-card>
                <v-toolbar dark>
                    <v-toolbar-title>{{ pendingRequests.length }} Pending Requests</v-toolbar-title>
                    <v-spacer></v-spacer>
                    <v-btn icon dark @click="closeDialog"><v-icon>mdi-close</v-icon></v-btn>
                </v-toolbar>
                <v-card-text class="mt-4">
<!--                    {{ pendingRequests }}-->
                    <v-list dense two-lines>
                        <v-list-item v-for="(req, index) in pendingRequests" :key="'req-pending-attention-' + index">
                            <v-list-item-title>{{ req.date_requested | slashdatedow }}</v-list-item-title>
                            <v-list-item-subtitle>{{ req.requester.first_name + ' ' + req.requester.last_name }}</v-list-item-subtitle>
                            <v-list-item-icon>
                                <admin-manage-vacation-request-modal action="pending" :user="user" :vacation-request="req"></admin-manage-vacation-request-modal>
                                <admin-manage-vacation-request-modal action="deny" :user="user" :vacation-request="req"></admin-manage-vacation-request-modal>
                            </v-list-item-icon>
                        </v-list-item>
                    </v-list>
                </v-card-text>
            </v-card>
        </v-dialog>
    </div>
</template>
<script>
	import Vue from 'vue'
    import AdminManageVacationRequestModal from "./AdminManageVacationRequestModal"
    export default {
        components: { AdminManageVacationRequestModal },
		props: {
			user: {
                type: Object,
                required: true
            },
            pendingRequests: {
				type: Array,
                required: true
            }
		},
        created() {
        },
		data: () => ({
            dialog: false
		}),
        computed: {
        },
        methods: {
			// Do a little cleanup when dialog closes
			closeDialog() {
                this.dialog = false
            }
        },
        watch: {
        }
	}
</script>

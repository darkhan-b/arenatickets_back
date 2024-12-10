<template>
    <div>
        <div class="container-fluid" v-if="fade">
            <div class="row align-items-center">
                <div class="col-6">
                    <h6 class="">{{ trans("order") }} #{{ order.id }}</h6>
                </div>
                <div class="col-6 text-right">
                    <a class="btn btn-themed btn-outline-secondary" href="/admin#/order">Назад</a>
                    <button v-if="order.status == 'authorized'" type="submit" class="btn btn-themed btn-primary ml-2" @click="approveOrder">{{ trans("approve") }}</button>
                    <button v-if="order.status == 'authorized'" type="submit" class="btn btn-themed btn-danger ml-2" @click="cancelOrder">{{ trans("cancel") }}</button>
                    <button v-if="order.status == 'new' || order.status == 'waiting_payment'" type="submit" class=" ml-2 btn btn-themed btn-danger" @click="cancelOrder">Отменить</button>
                    <button v-if="order.status == 'waiting_payment'" type="submit" class="btn btn-themed btn-primary ml-2" @click="transferOrder">{{ trans("Провести оплату") }}</button>
                    <button v-if="order.status == 'paid'" type="submit" class="btn btn-themed btn-danger ml-2" @click="returnOrder">{{ trans("refund") }}</button>
                    <button v-if="order.status == 'paid' || order.status == 'delivered' || order.status == 'finished'" class="btn btn-themed btn-info ml-2" @click="mailSend">{{ trans("send by email") }}</button>
                    <!--                    <a href="{{url('admin/orders/'.$order->id.'/edit')}}" class="btn btn-info">{{ trans("Edit") }}</a>-->
                </div>
            </div>
            <hr>



            <div class="row">
                <div class="col-md-6">
                    <div class="card ">
                        <div class="px-3 pt-3 pb-0">
                            <h6 class="mb-0">Данные заказа</h6>
                        </div>
                        <hr>
                        <div class="content p-3">
                            <table class="table no-first-border-top">
                                <tr>
                                    <td>{{ trans("status") }}</td>
                                    <td><span :class="'badge-'+order.status" class="badge">{{ trans(order.status) }}</span></td>
                                </tr>
                                <tr>
                                    <td>Email</td>
                                    <td>{{ order.email }}</td>
                                </tr>
                                <tr>
                                    <td>{{ trans("phone") }}</td>
                                    <td>{{ order.phone }}</td>
                                </tr>
                                <tr>
                                    <td>{{ trans("name") }}</td>
                                    <td>{{ order.name }}</td>
                                </tr>
                                <tr>
                                    <td>{{ trans("address") }}</td>
                                    <td>
                                        {{ order.full_address }}
                                    </td>
                                </tr>
                                <tr>
                                    <td>{{ trans("comments") }}</td>
                                    <td>{{ order.comments }}</td>
                                </tr>
                                <tr>
                                    <td>Дата заказа</td>
                                    <td>{{ order.created_at }}</td>
                                </tr>
                                <tr>
                                    <td>{{ trans("promocode") }}</td>
                                    <td>{{ order.promocode }}</td>
                                </tr>
                                <tr>
                                    <td>Метод оплаты</td>
                                    <td>{{ trans(order.pay_method) }}</td>
                                </tr>
                                <tr>
                                    <td>Доставка</td>
                                    <td>{{ transName(order.delivery_method.title) }} <span v-if="order.pickup_point">из {{ order.pickup_point.address_ru }}</span></td>
                                </tr>
                                <tr>
                                    <td>Id оплаты</td>
                                    <td>{{ order.pay_id }}</td>
                                </tr>
                                <tr>
                                    <td>Дата оплаты</td>
                                    <td>{{ order.pay_date }}</td>
                                </tr>
                                <tr>
                                    <td>Сумма оплаты</td>
                                    <td>{{ order.pay_summ | formatNumber }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="card">
                        <div class="px-3 pt-3 pb-0">
                            <h6 class="mb-0">{{ trans("products") }}</h6>
                        </div>
                        <hr>
                        <div class="content p-3">
                            <table class="table table-striped no-first-border-top">
                                <thead>
                                <tr>
                                    <th>Товар</th>
                                    <th>{{ trans("quantity") }}</th>
                                    <th>{{ trans("price") }}</th>
                                    <th>{{ trans("total") }}</th>
                                </tr>
                                </thead>
                                <tr v-for="(oi, oiind) in order.order_items">
                                    <td>
                                        <div>{{ oi.product.title_ru }}</div>
                                        <div>Артикул: {{ oi.product.artikul }}</div>
                                        <div class="text-muted" style="line-height: 1">
                                            <small>{{ oi.options_string }}</small>
                                        </div>
                                    </td>
                                    <td>
                                        <input type="text" class="text-center bg-light border-0" v-model="order.order_items[oiind].quantity"/>
<!--                                        {{ oi.quantity }}-->
                                    </td>
                                    <td class="nobr">
                                        {{ (parseInt(oi.price) + parseInt(oi.discounted)) | formatNumber }}
                                        <span v-if="oi.discounted > 0">
                                        <br><span  class="text-muted f-13">-{{ oi.discounted | formatNumber }}</span>
                                        </span>
                                    </td>
                                    <td class="nobr">{{ (oi.price * oi.quantity) | formatNumber() }}</td>
                                </tr>
                                <tr>
                                    <td>Общая скидка {{ order.promocode_model ? '(промокод ' +order.promocode_model.promocode+')' : '' }}</td>
                                    <td></td>
                                    <td>({{ order.general_discount | formatNumber }}%)</td>
                                    <td class="nobr">{{ (-(order.general_discount/100)*order.sum_before_general_discount) | formatNumber }}</td>
                                </tr>
                                <tr>
                                    <td>{{ trans("delivery") }}<br>{{ transName(order.delivery_method.title) }}</td>
                                    <td></td>
                                    <td></td>
                                    <td class="nobr">{{ order.delivery_cost | formatNumber }}</td>
                                </tr>
                                <tr>
                                    <td><b>{{ trans("total") }}</b></td>
                                    <td colspan="2"></td>
                                    <td class="nobr"><b class="color-blue">{{ order.total_sum | formatNumber }}</b></td>
                                </tr>
                                <tr>
                                    <td colspan="3">Бонусов использовано</td>
                                    <td>{{ order.bonus_used | formatNumber }}</td>
                                </tr>
                                <tr>
                                    <td colspan="3"><b>К оплате</b></td>
                                    <td><b class="color-blue">{{ order.sum_to_pay | formatNumber }}</b></td>
                                </tr>
                                <!--                                <tr>-->
                                <!--                                    <td colspan="3">Бонусов начислено</td>-->
                                <!--                                    <td>{{ order.bonus_used | formatNumber }}</td>-->
                                <!--                                </tr>-->
                            </table>
                            <div class="text-right">
                                <button v-if="order_changed" @click="updateOrderItems"  class="btn btn-themed btn-primary">Сохранить</button>
                            </div>
                        </div>

                    </div>


                    <div class="card mt-4">
                        <div class="px-3 pt-3 pb-0">
                            <h6 class="mb-0">Зачисление бонусов за заказ</h6>
                        </div>
                        <hr>
                        <div class="content p-3">
                            <template v-if="order.bonus_transactions">
                                <table class="table no-first-border-top">
                                    <thead>
                                    <tr>
                                        <th>Бонус</th>
                                        <th>Тип</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <tr v-for="bt in order.bonus_transactions">
                                        <td>{{ bt.bonus | formatNumber }}</td>
                                        <td>{{ trans(bt.type) }}</td>
                                    </tr>
                                    </tbody>
                                </table>
                            </template>
                            <template v-else>
                                Зачисление бонусов не предусмотрено
                            </template>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>


    export default {
        mounted() {
            this.fade = false
            this.loadOrder()
        },
        methods: {
            loadOrder() {
                axios.get('/admin/order/'+this.$route.params.orderid+'/details').then(res => {
                    this.fade = true
                    this.order = res.data
                })
            },
            approveOrder() {
                axios.post('/admin/order/'+this.$route.params.orderid+'/approve').then(res => {
                    if(res.data.success) {
                        this.order = res.data.order
                        window.noty(this.trans('success_message'),'success')
                    }
                })
            },
            cancelOrder() {
                axios.post('/admin/order/'+this.$route.params.orderid+'/cancel').then(res => {
                    if(res.data.success) {
                        this.order = res.data.order
                        window.noty(this.trans('success_message'),'success')
                    }

                })
            },
            returnOrder() {
                if(confirm('Вы уверены?')) {
                    axios.post('/admin/order/'+this.$route.params.orderid+'/return').then(res => {
                        if(res.data.success) {
                            this.order = res.data.order
                            window.noty(this.trans('success_message'),'success')
                        }
                    })
                }
            },
            transferOrder() {
                if(confirm('Вы уверены, что оплата была полностью получена?')) {
                    axios.post('/admin/order/'+this.$route.params.orderid+'/transfer_payment').then(res => {
                        if(res.data.success) {
                            this.order = res.data.order
                            window.noty(this.trans('success_message'),'success')
                        }
                    })
                }
            },
            updateOrderItems() {
                axios.post('/admin/order/'+this.$route.params.orderid+'/orderitems/change', { items: this.order.order_items }).then(res => {
                    if(res.data.success) {
                        this.order = res.data.order
                        window.noty(this.trans('success_message'),'success')
                    }
                })
            },
            mailSend() {
                axios.post('/admin/order/'+this.$route.params.orderid+'/mail/send').then(res => {
                    window.noty(this.trans('success_message'),'success')
                })
            },
        },
        watch:{
            $route (to, from) {

            },
            order_items: {
                handler: function (val, oldval) {
                    if (oldval != null) {
                        this.order_changed = true
                    }
                },
                deep: true
            }
        },
        computed: {
            order_items() {
                return this.order ? this.order.order_items : null
            }
        },
        props: {
        },
        data: function() {
            return {
                order: null,
                fade: false,
                order_changed: false

            }
        }
    }
</script>

<style scoped>

</style>
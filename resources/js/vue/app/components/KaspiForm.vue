<template>
  <div>
    <form action="https://kaspi.kz/online" method="post" ref="kaspiform" v-if="order">
      <input type="hidden" name="TranId" :value="order.id">
      <input type="hidden" name="OrderId" :value="order.id">
      <input type="hidden" name="Amount" :value="(order.price * 100)">
      <input type="hidden" name="Service" value="Topbilet">
      <input type="hidden" name="returnUrl" :value="`https://api.topbilet.kz/order/kaspi-success/${order.id}`">
      <button class="btn btn-danger" style="display: none;" type="submit"></button>
  </form>

  </div>
</template>

<script>
export default {
  name: "KaspiForm",
  props: {
    order: {},
  },
  methods: {
    submit() {
      this.$refs.kaspiform.submit()
    },
    // async getQR() {
    //   try {
    //     let res = await axios.post(`https://kaspi.kz/online`, {
    //       TranId: this.order.id,
    //       OrderId: this.order.id,
    //       Amount: (this.order.price * 100),
    //       Service: "Topbilet",
    //       returnUrl: `https://api.topbilet.kz/order/kaspi-success/${this.order.id}`,
    //       refererHost: "topbilet.kz",
    //       GenerateQrCode: true
    //     }, {
    //       headers: {}
    //     })
    //     console.log(res)
    //     console.log(res.data)
    //   } catch (e) {
    //     console.log(e)
    //   }
    // }
  }
}
</script>

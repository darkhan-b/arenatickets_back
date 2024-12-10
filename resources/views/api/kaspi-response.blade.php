<?xml version="1.0" encoding="UTF-8"?>
<response>
    <txn_id>{{ $txn_id ?? '' }}</txn_id>
    <result>{{ $result_code ?? 5 }}</result>
    @if(isset($command) && $command == 'pay')
        <prv_txn>{{ $prv_txn ?? '' }}</prv_txn>
    @endif
    <fields>
        <field1 name="name">{{ $userName ?? '' }}</field1>
        <field2 name="show">{{ $showName ?? '' }}</field2>
    </fields>
    <sum>{{ $sum ?? 0 }}</sum>
    <comment>{{ $comment ?? '' }}</comment>
</response>


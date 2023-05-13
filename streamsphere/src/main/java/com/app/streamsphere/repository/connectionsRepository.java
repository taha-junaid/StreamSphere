package com.app.streamsphere.repository;

import com.amazonaws.services.dynamodbv2.datamodeling.DynamoDBMapper;
import com.amazonaws.services.dynamodbv2.datamodeling.DynamoDBQueryExpression;
import com.amazonaws.services.dynamodbv2.model.AttributeValue;
import com.app.streamsphere.models.ConnectionsModel;
import com.app.streamsphere.models.UserEventsModel;
import org.springframework.stereotype.Repository;

import java.util.HashMap;
import java.util.List;
import java.util.Map;
import java.util.stream.Collectors;

@Repository
public class connectionsRepository {
    private final DynamoDBMapper dynamoDBMapper;

    public connectionsRepository(DynamoDBMapper dynamoDBMapper) {
        this.dynamoDBMapper = dynamoDBMapper;
    }

    public boolean doesChannelIdExists (String channelId){

        Map<String, AttributeValue> eav = new HashMap<>();
        eav.put(":val1", new AttributeValue().withS(channelId));

        DynamoDBQueryExpression<ConnectionsModel> queryExpression = new DynamoDBQueryExpression<ConnectionsModel>()
                .withKeyConditionExpression("channelID = :val1")
                .withExpressionAttributeValues(eav)
                .withIndexName("channelID-index")
                .withConsistentRead(false);

        List<ConnectionsModel> channels = dynamoDBMapper.query(ConnectionsModel.class, queryExpression);

        long connections = channels.stream()
                .map(ConnectionsModel::getConnectionId)
                .collect(Collectors.toSet())
                .size();

        return connections>0;
    }
}

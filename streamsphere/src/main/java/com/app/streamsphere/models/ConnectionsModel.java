package com.app.streamsphere.models;

import com.amazonaws.services.dynamodbv2.datamodeling.DynamoDBAttribute;
import com.amazonaws.services.dynamodbv2.datamodeling.DynamoDBHashKey;
import com.amazonaws.services.dynamodbv2.datamodeling.DynamoDBTable;
import com.fasterxml.jackson.annotation.JsonProperty;
import lombok.*;

@Getter
@Setter
@AllArgsConstructor
@NoArgsConstructor
@DynamoDBTable(tableName = "chat-ConnectionsTable8000B8A1-1UN2ULTF26C2F")
@Data
public class ConnectionsModel {
    @JsonProperty("connectionId")
    @DynamoDBHashKey(attributeName = "connectionId")
    String connectionId;
    @JsonProperty("channelID")
    @DynamoDBAttribute(attributeName = "channelID")
    String channelID;

    @JsonProperty("userID")
    @DynamoDBAttribute(attributeName = "userID")
    String userID;
}

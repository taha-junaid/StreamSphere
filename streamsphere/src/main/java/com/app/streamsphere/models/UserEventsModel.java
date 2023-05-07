package com.app.streamsphere.models;

import com.amazonaws.services.dynamodbv2.datamodeling.*;
import com.fasterxml.jackson.annotation.JsonProperty;
import lombok.*;

@Getter
@Setter
@AllArgsConstructor
@NoArgsConstructor
@DynamoDBTable(tableName = "userEvents")
@Data
public class UserEventsModel {


    @JsonProperty("channelId")
    @DynamoDBHashKey(attributeName = "channelId")
    String channelId;

    @JsonProperty("eventTime")
    @DynamoDBRangeKey(attributeName = "eventTime")
    String eventTime;

    @JsonProperty("eventType")
    @DynamoDBAttribute(attributeName = "eventType")
    String eventType; // CREATED, JOIN, LEAVE, PLAY, PAUSE, SEEK

    @JsonProperty("userId")
    @DynamoDBAttribute(attributeName = "userId")
    String userId;

    @JsonProperty("movieId")
    @DynamoDBAttribute(attributeName = "movieId")
    String movieId;
}

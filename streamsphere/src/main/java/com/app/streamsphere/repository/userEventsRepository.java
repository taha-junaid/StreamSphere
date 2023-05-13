package com.app.streamsphere.repository;

import com.amazonaws.services.dynamodbv2.datamodeling.DynamoDBMapper;
import com.amazonaws.services.dynamodbv2.datamodeling.DynamoDBQueryExpression;
import com.amazonaws.services.dynamodbv2.model.AttributeValue;
import com.app.streamsphere.models.UserEventsModel;
import org.springframework.stereotype.Repository;

import java.util.stream.Collectors;
import java.util.HashMap;
import java.util.List;
import java.util.Map;
@Repository
public class userEventsRepository {
    private final DynamoDBMapper dynamoDBMapper;

    public userEventsRepository(DynamoDBMapper dynamoDBMapper) {
        this.dynamoDBMapper = dynamoDBMapper;
    }

    public List<String> getActiveUsersInChannel(String channelId) {

        Map<String, AttributeValue> eav = new HashMap<>();
        eav.put(":val1", new AttributeValue().withS(channelId));

        DynamoDBQueryExpression<UserEventsModel> queryExpression = new DynamoDBQueryExpression<UserEventsModel>()
                .withKeyConditionExpression("channelId = :val1")
                .withExpressionAttributeValues(eav)
                .withConsistentRead(false);

        List<UserEventsModel> userEvents = dynamoDBMapper.query(UserEventsModel.class, queryExpression);

        //Filter the users based on eventType
        List<String> joinedUsers = userEvents.stream()
                .filter(u -> u.getEventType().equals("CREATED") || u.getEventType().equals("JOINED"))
                .map(UserEventsModel::getUserId)
                .collect(Collectors.toList());

        List<String> leftUsers = userEvents.stream()
                .filter(u -> u.getEventType().equals("LEFT"))
                .map(UserEventsModel::getUserId)
                .collect(Collectors.toList());

        // Subtract the leftUsers from joinedUsers
        joinedUsers.removeAll(leftUsers);

        return joinedUsers;
    }

    public List<String> getRecentMoviesForUser(String userId) {
        Map<String, AttributeValue> eav = new HashMap<>();
        eav.put(":val1", new AttributeValue().withS(userId));

        DynamoDBQueryExpression<UserEventsModel> queryExpression = new DynamoDBQueryExpression<UserEventsModel>()
                .withKeyConditionExpression("userId = :val1")
                .withExpressionAttributeValues(eav)
                .withIndexName("userId-eventTime-index")
                .withScanIndexForward(false)  // sort by descending order of eventTime
                .withConsistentRead(false)
                .withLimit(5);  // limit to top 5

        List<UserEventsModel> userEvents = dynamoDBMapper.query(UserEventsModel.class, queryExpression);

        List<String> recentMovies = userEvents.stream()
                .filter(u -> u.getEventType().equals("CREATED") || u.getEventType().equals("JOINED"))
                .map(UserEventsModel::getMovieId)
                .distinct() // get unique movies
                .collect(Collectors.toList());

        return recentMovies;
    }

}

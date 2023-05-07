package com.app.streamsphere.Kafka;


import com.amazonaws.services.dynamodbv2.datamodeling.DynamoDBMapper;
import com.amazonaws.services.dynamodbv2.datamodeling.DynamoDBMapper.FailedBatch;
import com.amazonaws.services.dynamodbv2.model.AmazonDynamoDBException;
import com.app.streamsphere.models.UserEventsModel;
import com.google.gson.Gson;
import org.apache.kafka.clients.consumer.ConsumerRecord;
import org.apache.kafka.clients.consumer.ConsumerRecords;
import org.slf4j.Logger;
import org.slf4j.LoggerFactory;
import org.springframework.stereotype.Service;
import org.springframework.scheduling.annotation.Scheduled;
import org.apache.kafka.clients.consumer.KafkaConsumer;

import java.time.Duration;
import java.util.ArrayList;
import java.util.Collections;
import java.util.List;
import java.util.Properties;

@Service
public class MyKafkaConsumer {
    private static final Logger LOGGER = LoggerFactory.getLogger(KafkaConsumer.class);
    private final DynamoDBMapper dynamoDBMapper;

    public MyKafkaConsumer(DynamoDBMapper dynamoDBMapper) {
        this.dynamoDBMapper = dynamoDBMapper;
    }

    @Scheduled(fixedDelay = 5000)
    public void consumeMessages() {
        Properties properties = new Properties();
        properties.setProperty("bootstrap.servers", "localhost:9092");
        properties.setProperty("group.id", "streamsphere");
        properties.setProperty("key.deserializer", "org.apache.kafka.common.serialization.StringDeserializer");
        properties.setProperty("value.deserializer", "org.springframework.kafka.support.serializer.JsonDeserializer");
        properties.setProperty("max.poll.records", "10");
        properties.setProperty("enable.auto.commit", "false");

        KafkaConsumer<String, String> consumer = new KafkaConsumer<>(properties);

        consumer.subscribe(Collections.singleton("user-events"));

        while (true) {
            ConsumerRecords<String, String> records = consumer.poll(Duration.ofSeconds(5));
            List<UserEventsModel> userEventsList = new ArrayList<>();
            for (ConsumerRecord<String, String> record : records) {
                Gson gson = new Gson();
                UserEventsModel userEvent = gson.fromJson(record.value(), UserEventsModel.class);
                userEventsList.add(userEvent);
            }
//            LOGGER.info("~~~~~~~~~~ CONSUMED "+userEventsList.size()+ " RECORDS FROM KAFKA TOPIC user-events ~~~~~~~~");
            if (!userEventsList.isEmpty()) {
                boolean batchSaveSuccess = false;
                List<FailedBatch> failedBatches = null;
                try {
                    failedBatches = dynamoDBMapper.batchSave(userEventsList);
                } catch (AmazonDynamoDBException e) {
                    // Handle exception
                    LOGGER.error("Error saving items to DynamoDB: " + e.getMessage());
                }

                if (failedBatches == null || failedBatches.isEmpty()) {
                    batchSaveSuccess = true;
                } else {
                    // Handle failed batches
    //            LOGGER.info("~~~~~~~~~~ BATCH LOAD TO DYNAMODB FAILED ~~~~~~~~");
                }
                if (batchSaveSuccess) {
                    consumer.commitAsync();
                } else {
                    // Don't commit the message to the consumer
                }
            }
        }
    }
}
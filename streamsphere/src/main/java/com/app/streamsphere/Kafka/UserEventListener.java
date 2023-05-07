package com.app.streamsphere.Kafka;

import com.app.streamsphere.models.UserEventsModel;
import com.app.streamsphere.models.UserEvent;
import com.google.gson.Gson;
import com.google.gson.GsonBuilder;
import org.springframework.beans.factory.annotation.Value;
import org.springframework.context.ApplicationListener;
import org.springframework.kafka.core.KafkaTemplate;
import org.springframework.stereotype.Component;

@Component
public class UserEventListener implements ApplicationListener<UserEvent> {

    @Value("${spring.kafka.topic.name}")
    private String topicName;

    private KafkaTemplate<String, String> kafkaTemplate;
    public UserEventListener(KafkaTemplate<String, String> kafkaTemplate) {
        this.kafkaTemplate = kafkaTemplate;
    }

    @Override
    public void onApplicationEvent(UserEvent event) {
        UserEventsModel km= new UserEventsModel(event.getChannelId(), event.getEventTime(), event.getEventType().name().toString(),  event.getUserId(),  event.getMovieId());
        Gson gson = new GsonBuilder().setPrettyPrinting().disableHtmlEscaping().create();
        String json = gson.toJson(km);
        kafkaTemplate.send(topicName, json);
    }
}
